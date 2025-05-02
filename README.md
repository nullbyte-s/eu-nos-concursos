# Eu nos Concursos

O projeto "Eu nos Concursos" é uma aplicação web que realiza consultas na internet em busca de novas publicações pertinentes aos concurseiros, facilitando o acompanhamento de convocações em publicações oficiais.

## Funcionalidades

- **Consulta Diária**: Realiza consultas periódicas na internet para verificar se novos sites foram indexados.
- **Notificações**: Informa o usuário sobre novas publicações relevantes para concursos públicos.
- **Privacidade**: Respeita a privacidade dos usuários e utiliza fontes de informação legítimas.

## Tecnologias Utilizadas

- **PHP**: Backend da aplicação.
- **SQLite**: Armazenamento de dados.
- **HTML/CSS/JavaScript**: Interface do usuário.

---

## Integração Contínua com Jenkins + Docker

Este projeto utiliza uma pipeline de integração contínua com **Jenkins** e **Docker** para facilitar testes e deploy automáticos a partir de *pushes* no GitHub.

### Instalando o Jenkins com Docker

Execute o comando abaixo para subir o Jenkins em um container Docker, com acesso ao Docker do host (é preciso montar o socket Docker do host dentro do container Jenkins, em /var/run/docker.sock - isto é uma prática necessária para builds):

```bash
docker run -d \
  -u root \
  --name jenkins \
  --add-host=host.docker.internal:host-gateway \
  -p 8090:8080 -p 50000:50000 \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v jenkins_home:/var/jenkins_home \
  jenkins/jenkins:lts
```

Acesse o Jenkins em: [http://localhost:8090](http://localhost:8090)

Obtenha a senha de administrador inicial com:

```bash
docker ps
docker logs -f <CONTAINER_ID>
```

### Instalações adicionais no Jenkins

- Instale os plugins sugeridos na tela de boas-vindas do Jenkins.
- Adicionalmente, instale os plugins: **Docker Pipeline** e **GitHub Integration Plugin**.

Para permitir que o Jenkins execute comandos Docker dentro do container:

```bash
docker exec -it jenkins bash
apt-get update
apt-get install -y docker.io
exit
docker restart jenkins
```

---

### Pipeline automatizado via GitHub (CI)

#### 1. Suba os arquivos de configuração para o repositório:

- `Dockerfile`
- `Jenkinsfile`
- Arquivos de configuração do nginx e supervisord (em uma pasta `docker/`)

#### 2. Crie o job no Jenkins

1. Acesse o Jenkins: [http://localhost:8090](http://localhost:8090)
2. Clique em **"New Item"**
3. Nomeie o job (ex: `eunosconcursos-pipeline`)
4. Selecione **"Pipeline"** e clique em **OK**

##### Configurações:

- **General**
  - (Opcional) Marque "GitHub project" e adicione a URL do repositório
- **Pipeline**
  - **Definition**: *Pipeline script from SCM*
  - **SCM**: Git
  - **Repository URL**: `https://github.com/nullbyte-s/eu-nos-concursos.git`
  - Adicione credenciais caso o repositório seja privado
  - **Branch**: `*/main`
  - **Script Path**: `Jenkinsfile`

Salve e clique em **Build Now** para testar.

---

### Webhook GitHub → Jenkins

Para builds automáticos ao fazer push no GitHub:

#### Pré-requisitos

- Jenkins deve estar acessível via rede/local ou internet (ex: `http://localhost:8090`, ou via `ngrok`)
- O plugin **GitHub Integration Plugin** deve estar instalado
- O job do Jenkins deve estar configurado com:
  - "GitHub hook trigger for GITScm polling" **ativado**
  - **"Poll SCM" desativado**

#### Etapas para configurar o webhook:

1. Sugestão para publicar seu servidor local rapidamente na internet - ngrok:
   ```bash
   ngrok http 8090
   ```
   Isso vai gerar algo como: `https://abcd1234.ngrok-free.app`

   _**Nota:** As configurações na página do ngrok permitem criar um subdomínio customizado, o que é recomendado para fins de reuso._

2. No Jenkins:
   - Vá no job → **Configurar** → **Build Triggers**
   - Marque: **GitHub hook trigger for GITScm polling**

3. No GitHub:
   - Vá em **Settings** → **Webhooks** → **Add webhook**
   - Preencha os campos:

   | Campo         | Valor                                         |
   |---------------|-----------------------------------------------|
   | Payload URL   | `http://<PUBLIC_JENKINS_URL>/github-webhook/` |
   | Content type  | `application/json`                            |
   | Secret        | *(opcional)*                                  |
   | Events        | **Just the push event**                       |

   - Clique em **Add webhook**

4. Teste com um `git push` no repositório. O build deve iniciar automaticamente no Jenkins.

---

### Testando manualmente o container

Se necessário, você pode testar a imagem Docker gerada localmente com:

```bash
docker build -t eunosconcursos:latest .
docker run -d -p 8082:80 eunosconcursos:latest
curl -I http://localhost:8082
```

---

- [Registros de execução do pipeline](https://github.com/nullbyte-s/eu-nos-concursos/tree/main/archives/img)

---

<h5 align="center">
  Made with 💜 by <a href="https://github.com/nullbyte-s/">nullbyte-s</a><br>
  <a href="https://choosealicense.com/licenses/mit/"><br>
  <img src="https://img.shields.io/badge/License-MIT-green.svg">
  </a>
</h5>
