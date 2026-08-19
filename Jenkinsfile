pipeline {
    agent none

    environment {
        APP_NAME = "portfoliov3-api"
        DOCKER_IMAGE = "joanisky/portfoliov3-api"
        DOCKER_TAG = "${BUILD_NUMBER}"
        KUBE_NAMESPACE = "portfoliov3-api"
        KUBE_DEPLOYMENT = "symfony-api"
    }

    stages {
        stage('Checkout') {
            agent any
            steps {
                checkout scm
                script {
                    env.GIT_BRANCH_NAME = env.GIT_BRANCH ?: scm.branches[0].name
                    echo "Build: ${BUILD_NUMBER} — Branche: ${env.GIT_BRANCH_NAME}"
                }
            }
        }
        stage('Tests') {
            agent {
                kubernetes {
                    yamlFile 'k8s/jenkins/php-test-pod.yaml'
                    defaultContainer 'php'
                }
            }
            steps {
                container('php') {
                    withCredentials([
                        string(credentialsId: 'github-composer-token', variable: 'GITHUB_TOKEN')
                    ]) {
                    sh '''
                        git config --global --add safe.directory '*'
                        export COMPOSER_AUTH="{\\"github-oauth\\":{\\"github.com\\":\\"$GITHUB_TOKEN\\"}}"

                        echo "Attente de MariaDB..."
                        TRIES=0
                        until php -r 'try { new PDO("mysql:host=127.0.0.1;port=3306;dbname=portfoliov3_api_db_test", "test", "test"); exit(0); } catch (Exception $e) { echo $e->getMessage() . PHP_EOL; exit(1); }'; do
                            TRIES=$((TRIES+1))
                            if [ $TRIES -ge 20 ]; then
                                echo "MariaDB inaccessible apres 20 tentatives, abandon."
                                exit 1
                            fi
                            echo "Tentative $TRIES/20, nouvelle tentative dans 3s..."
                            sleep 3
                        done
                        echo "MariaDB prete."

                        composer install --prefer-dist --no-progress --no-interaction

                        echo "APP_ENV=test" > .env.test.local
                        echo "DATABASE_URL=${DATABASE_URL}" >> .env.test.local

                        php bin/console doctrine:database:create --env=test --if-not-exists --no-interaction
                        php bin/console doctrine:schema:create --env=test --no-interaction

                        mkdir -p test-results/phpunit
                        APP_ENV=test ./vendor/bin/phpunit \
                            --log-junit test-results/phpunit/junit.xml \
                            --testdox
                    '''
                    }
                }
            }
            post {
                always {
                    stash includes: 'test-results/phpunit/**', name: 'phpunit-reports', allowEmpty: true
                }
            }
        }
        stage('Publish Reports') {
            agent any
            options {
                skipDefaultCheckout true
            }
            steps {
                unstash 'phpunit-reports'
                junit allowEmptyResults: true, testResults: 'test-results/phpunit/junit.xml'
            }
            post {
                always {
                    cleanWs()
                }
            }
        }
        stage('Build & Push Docker Image') {
            agent any
            steps {
                withCredentials([
                    usernamePassword(credentialsId: 'jenkins-dockerhub', usernameVariable: 'DOCKER_USER', passwordVariable: 'DOCKER_PASS'),
                    string(credentialsId: 'github-composer-token', variable: 'GITHUB_TOKEN')
                ]) {
                    sh '''
                        docker login -u $DOCKER_USER -p $DOCKER_PASS

                        docker build \
                            -f .docker/Dockerfile \
                            --target prod \
                            --build-arg APP_ENV=prod \
                            --build-arg APP_SECRET=dummysecret \
                            --build-arg COMPOSER_AUTH="{\\"github-oauth\\":{\\"github.com\\":\\"$GITHUB_TOKEN\\"}}" \
                            -t ${DOCKER_IMAGE}:${DOCKER_TAG} .

                        docker push ${DOCKER_IMAGE}:${DOCKER_TAG}

                        docker tag ${DOCKER_IMAGE}:${DOCKER_TAG} ${DOCKER_IMAGE}:latest
                        docker push ${DOCKER_IMAGE}:latest
                    '''
                }
            }
        }
        stage('Deploy Secrets') {
            agent any
            when { expression { env.GIT_BRANCH == 'origin/main' } }
            steps {
                withCredentials([
                    file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE'),
                    string(credentialsId: 'portfoliov3-db-root-password', variable: 'DB_ROOT_PASS'),
                    string(credentialsId: 'portfoliov3-db-user-password', variable: 'DB_USER_PASS'),
                    string(credentialsId: 'portfoliov3-app-secret', variable: 'APP_SECRET'),
                    string(credentialsId: 'portfoliov3-mailer-dsn', variable: 'MAILER_DSN'),
                    string(credentialsId: 'portfoliov3-contact-email', variable: 'CONTACT_EMAIL')
                ]) {
                    sh '''
                        export KUBECONFIG=$KUBECONFIG_FILE

                        kubectl create namespace ${KUBE_NAMESPACE} --dry-run=client -o yaml | kubectl apply -f -

                        kubectl create secret generic mariadb-secret \
                            --from-literal=root-password=${DB_ROOT_PASS} \
                            --from-literal=user-password=${DB_USER_PASS} \
                            -n ${KUBE_NAMESPACE} \
                            --dry-run=client -o yaml | kubectl apply -f -

                        kubectl create secret generic symfony-env \
                            --from-literal=APP_SECRET=${APP_SECRET} \
                            --from-literal=DATABASE_URL="mysql://portfoliov3_api_user:${DB_USER_PASS}@mariadb:3306/portfoliov3_api_db?serverVersion=10.11.4-MariaDB&charset=utf8mb4" \
                            --from-literal=MAILER_DSN=${MAILER_DSN} \
                            --from-literal=CONTACT_EMAIL=${CONTACT_EMAIL} \
                            -n ${KUBE_NAMESPACE} \
                            --dry-run=client -o yaml | kubectl apply -f -
                    '''
                }
            }
        }
        stage('Deploy Mysqld Exporter Secret') {
            agent any
            when { expression { env.GIT_BRANCH == 'origin/main' } }
            steps {
                withCredentials([
                    file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE'),
                    string(credentialsId: 'portfoliov3-mysqld-exporter-password', variable: 'EXPORTER_PASS')
                ]) {
                    sh '''
                        export KUBECONFIG=$KUBECONFIG_FILE
                        kubectl create secret generic mysqld-exporter-secret \
                            --from-literal=DATA_SOURCE_NAME="exporter:$EXPORTER_PASS@tcp(mariadb:3306)/" \
                            -n ${KUBE_NAMESPACE} \
                            --dry-run=client -o yaml | kubectl apply -f -
                        kubectl rollout restart deployment/mysqld-exporter -n ${KUBE_NAMESPACE} || true
                    '''
                }
            }
        }
        stage('Deploy PodMonitors') {
            agent any
            when { expression { env.GIT_BRANCH == 'origin/main' } }
            steps {
                withCredentials([
                    file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE')
                ]) {
                    sh '''
                        export KUBECONFIG=$KUBECONFIG_FILE
                        kubectl apply -k k8s/monitoring/
                    '''
                }
            }
        }
        stage('Deploy Grafana Alert Rules') {
            agent any
            when { expression { env.GIT_BRANCH == 'origin/main' } }
            steps {
                withCredentials([
                    file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE')
                ]) {
                    sh '''
                        export KUBECONFIG=$KUBECONFIG_FILE
                        kubectl create configmap portfoliov3-alert-rules \
                            --from-file=grafana-alert-rules.yaml=k8s/monitoring/grafana-alert-rules.yaml \
                            -n monitoring \
                            --dry-run=client -o yaml \
                            | kubectl label --local -f - grafana_alert=1 -o yaml \
                            | kubectl apply -f -
                    '''
                }
            }
        }
        stage('Deploy to Kubernetes') {
            agent any
            when {
                expression { env.GIT_BRANCH == 'origin/main' }
            }
            steps {
                script {
                    withCredentials([
                        file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE')
                    ]) {
                        sh '''
                            export KUBECONFIG=$KUBECONFIG_FILE

                            echo "Application des manifests Kubernetes..."
                            kubectl apply -k k8s/ -n ${KUBE_NAMESPACE}

                            echo "Mise a jour de l image vers: ${DOCKER_TAG}"
                            kubectl set image deployment/${KUBE_DEPLOYMENT} \
                                app=${DOCKER_IMAGE}:${DOCKER_TAG} \
                                -n ${KUBE_NAMESPACE}

                            echo "Attente du rollout..."
                            kubectl rollout status deployment/${KUBE_DEPLOYMENT} \
                                -n ${KUBE_NAMESPACE} \
                                --timeout=300s
                        '''
                    }
                }
            }
        }
        stage('Database Migrations') {
            agent any
            when {
                expression { env.GIT_BRANCH == 'origin/main' }
            }
            steps {
                script {
                    withCredentials([
                        file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE')
                    ]) {
                        sh '''
                            export KUBECONFIG=$KUBECONFIG_FILE

                            echo "Mise a jour de la base de donnees..."

                            kubectl exec deployment/${KUBE_DEPLOYMENT} -n ${KUBE_NAMESPACE} -- \
                                php bin/console doctrine:database:create --if-not-exists --env=prod || true

                            kubectl exec deployment/${KUBE_DEPLOYMENT} -n ${KUBE_NAMESPACE} -- \
                                php bin/console doctrine:schema:update --force --env=prod || true

                            kubectl exec deployment/${KUBE_DEPLOYMENT} -n ${KUBE_NAMESPACE} -- \
                                rm -rf /var/www/html/var/cache/prod || true

                            kubectl exec deployment/${KUBE_DEPLOYMENT} -n ${KUBE_NAMESPACE} -- \
                                php bin/console cache:clear --env=prod
                        '''
                    }
                }
            }
        }
        stage('Verify Deployment') {
            agent any
            when {
                expression { env.GIT_BRANCH == 'origin/main' }
            }
            steps {
                withCredentials([
                    file(credentialsId: 'kubeconfig', variable: 'KUBECONFIG_FILE')
                ]) {
                    sh '''
                        export KUBECONFIG=$KUBECONFIG_FILE

                        echo "=== Pods ==="
                        kubectl get pods -n ${KUBE_NAMESPACE}

                        echo "=== Services ==="
                        kubectl get svc -n ${KUBE_NAMESPACE}

                        echo "=== Ingress ==="
                        kubectl get ingress -n ${KUBE_NAMESPACE}

                        echo "=== Secrets ==="
                        kubectl get secrets -n ${KUBE_NAMESPACE}

                        echo "=== ConfigMaps ==="
                        kubectl get configmaps -n ${KUBE_NAMESPACE}
                    '''
                }
            }
        }
        stage('Health Check') {
            agent any
            steps {
                script {
                    timeout(time: 2, unit: 'MINUTES') {
                        waitUntil {
                            script {
                                def response = sh(
                                    script: 'curl -s -o /dev/null -w "%{http_code}" https://api.jonathanlore.fr/api',
                                    returnStdout: true
                                ).trim()

                                if (response == '200') {
                                    echo "API accessible"
                                    return true
                                } else {
                                    echo "En attente... (${response})"
                                    sleep 5
                                    return false
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    post {
        always {
            node('built-in') {
                sh "docker rmi ${DOCKER_IMAGE}:${DOCKER_TAG} || true"
                sh "docker rmi ${DOCKER_IMAGE}:latest || true"
            }
        }
        success {
            withCredentials([string(credentialsId: 'discord-webhook-url', variable: 'DISCORD_URL')]) {
                discordSend(
                    webhookURL: DISCORD_URL,
                    title: "Deploiement Reussi : ${APP_NAME}",
                    link: env.BUILD_URL,
                    result: 'SUCCESS',
                    description: "Le build #${env.BUILD_NUMBER} a ete deploye avec succes sur Kubernetes.\n**Branche:** ${env.GIT_BRANCH_NAME}"
                )
            }
        }
        failure {
            withCredentials([string(credentialsId: 'discord-webhook-url', variable: 'DISCORD_URL')]) {
                discordSend(
                    webhookURL: DISCORD_URL,
                    title: "Echec du Pipeline : ${APP_NAME}",
                    link: env.BUILD_URL,
                    result: 'FAILURE',
                    description: "Le build #${env.BUILD_NUMBER} a echoue.\nConsulte les logs ici : ${env.BUILD_URL}console"
                )
            }
        }
    }
}
