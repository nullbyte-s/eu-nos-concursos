pipeline {
    agent any

    stages {
        stage('Clonar Repositório') {
            steps {
                checkout scm
            }
        }

        stage('Construir imagem do Docker') {
            steps {
                script {
                    dockerImage = docker.build('eunosconcursos:latest')
                }
            }
        }

        stage('Testar Container') {
            steps {
                script {
                    sh '''
                        echo "Removendo container de teste, se existir..."
                        docker ps -a -q --filter "name=eunosconcursos_test" | xargs -r docker rm -f

                        echo "Iniciando container de teste..."
                        docker run -d --name eunosconcursos_test -p 8081:80 eunosconcursos:latest
                        echo "Aguardando o container iniciar..."
                        sleep 10
                        
                        echo "Testando endpoint..."
                        curl -I http://host.docker.internal:8081 || (echo "FALHA NO TESTE" && exit 1)
                        sleep 10
                        
                        echo "Removendo container de teste..."
                        docker stop eunosconcursos_test
                        docker rm eunosconcursos_test
                    '''
                }
            }
        }
    }
}
