# API de Barbearia — Teste Técnico

API REST desenvolvida em Laravel para gerenciamento de clientes, administradores, barbeiros e agendamentos.

## Tecnologias utilizadas

- PHP 8.2 ou superior
- Laravel 12
- MySQL ou MariaDB
- Laravel Sanctum para autenticação por Bearer Token
- L5-Swagger / OpenAPI para documentação da API
- Filas do Laravel para processamento assíncrono de e-mails

## Pré-requisitos

- PHP 8.2 ou superior;
- Composer;
- MySQL ou MariaDB;
- Git.

## Instalação e execução

1. Clone o repositório e entre na pasta do projeto:

   ```bash
   git clone https://github.com/alvesklvn/Teste-Next_Tecnologia.git
   cd Teste-Next_Tecnologia
   ```

2. Instale as dependências PHP:

   ```bash
   composer install
   ```

3. Crie o arquivo de ambiente e gere a chave da aplicação.

   **Linux/macOS:**

   ```bash
   cp .env.example .env
   ```

   **Windows:**

   ```bat
   copy .env.example .env
   ```

   Em seguida:

   ```bash
   php artisan key:generate
   ```

4. Crie um banco de dados MySQL ou MariaDB e ajuste as variáveis abaixo no arquivo `.env`:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=seu_banco
   DB_USERNAME=seu_usuario
   DB_PASSWORD=sua_senha
   ```

   O banco informado em `DB_DATABASE` deve existir antes da execução das migrations.

5. Configure o serviço de envio de e-mails. O arquivo `.env.example` contém uma configuração de referência para Gmail:

   ```dotenv
   MAIL_MAILER=smtp
   MAIL_SCHEME=null
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=seu-email@gmail.com
   MAIL_PASSWORD=sua-senha-de-aplicativo
   MAIL_FROM_ADDRESS=seu-email@gmail.com
   MAIL_FROM_NAME="${APP_NAME}"
   ```

   Para contas Gmail, utilize uma **senha de app** quando exigida pelas configurações de segurança da conta, e não a senha normal de acesso. Para outros provedores, substitua host, porta, usuário, senha e esquema pelos valores fornecidos pelo serviço de e-mail.

6. Execute as migrations e os seeders:

   ```bash
   php artisan migrate --seed
   ```

   O seeder inicial cadastra os tipos de usuário `admin` e `client`, um usuário administrador para o ambiente de desenvolvimento e um perfil de barbeiro associado a ele. Esses dados estão definidos em `database/seeders/UsersSeeder.php`.

   Antes de executar os seeders, confira o e-mail configurado para o administrador nesse arquivo e ajuste-o para um endereço que você possa acessar, caso queira receber as notificações. As credenciais iniciais também estão definidas no seeder.

7. Inicie a API:

   ```bash
   php artisan serve
   ```

   Por padrão, a aplicação ficará disponível em `http://127.0.0.1:8000`.

### Worker de filas

O projeto utiliza filas para enviar e-mails de verificação de conta e notificações de agendamento. Em outro terminal, mantenha um worker em execução:

```bash
php artisan queue:work --queue=verification,schedulings
```

Também é possível executar cada fila em um terminal separado:

```bash
php artisan queue:work --queue="verification"
```

```bash
php artisan queue:work --queue="schedulings"
```

Mantenha o worker ativo durante os testes que dependem do envio de e-mails.

## Documentação da API e Apidog

A API possui anotações OpenAPI nos controllers e utiliza o pacote L5-Swagger para gerar a especificação.

1. Com a aplicação instalada, gere a documentação:

   ```bash
   php artisan l5-swagger:generate
   ```

2. Com o servidor iniciado, visualize a interface Swagger UI em:

   ```text
   http://127.0.0.1:8000/api/documentation
   ```

3. A especificação OpenAPI em JSON fica disponível em:

   ```text
   storage/api-docs/api-docs.json
   ```

### Importar no Apidog

1. Gere a especificação seguindo os passos acima.
2. No Apidog, crie ou abra um projeto e escolha a opção de importar uma definição **OpenAPI/Swagger**.
3. Importe o arquivo local `storage/api-docs/api-docs.json`.
4. Configure o endereço base do ambiente no Apidog como `http://127.0.0.1:8000`.
5. Nas requisições protegidas, configure a autenticação Bearer Token com o token recebido no login. Também é possível informar o cabeçalho `Authorization` com o valor `Bearer SEU_TOKEN`.

## Endpoints principais

Todas as rotas abaixo utilizam o prefixo `/api`. As rotas protegidas utilizam autenticação via Laravel Sanctum. As rotas de administração também exigem um usuário com perfil `admin`.

| Método | Endpoint | Acesso / finalidade | Descrição |
|---|---|---|---|
| `POST` | `/auth/register` | Cadastro de cliente | Cadastra um cliente. |
| `GET` | `/auth/email/verify/{id}/{hash}` | Link assinado | Verifica o e-mail do usuário. O link é enviado por e-mail. |
| `POST` | `/auth/login` | Autenticação | Autentica o usuário e retorna `bearerToken`. |
| `POST` | `/auth/logout` | Autenticado | Invalida o token atual. |
| `GET` | `/admin` | Admin | Lista administradores. |
| `POST` | `/admin` | Admin | Cadastra um administrador, que pode também possuir perfil de barbeiro. |
| `GET` | `/admin/{id}` | Admin | Consulta um administrador. |
| `PATCH` | `/admin` | Admin | Atualiza os dados do administrador autenticado. |
| `DELETE` | `/admin` | Admin | Exclui a conta do administrador autenticado, respeitando a regra que impede excluir o único administrador. |
| `GET` | `/client` | Autenticado | Admin lista clientes; cliente consulta o próprio perfil. |
| `GET` | `/client/{id}` | Admin ou proprietário | Consulta um cliente. |
| `PATCH` | `/client/{id}` | Admin ou proprietário | Atualiza os dados de um cliente. |
| `DELETE` | `/client/{id}` | Admin ou proprietário | Exclui um cliente. |
| `GET` | `/scheduling` | Autenticado | Lista os agendamentos aos quais o usuário tem acesso. |
| `POST` | `/scheduling` | Autenticado | Cria um agendamento. |
| `GET` | `/scheduling/{id}` | Admin ou cliente responsável | Consulta um agendamento. |
| `PATCH` | `/scheduling/{id}` | Admin ou cliente responsável | Atualiza um agendamento. |
| `DELETE` | `/scheduling/{id}` | Admin ou cliente responsável | Cancela um agendamento. |

### Observações sobre permissões e dados

- O cadastro de cliente cria um usuário do tipo `client`.
- O login só permite autenticar usuários com e-mail verificado.
- No cadastro de cliente, os campos obrigatórios são `name`, `email`, `password`, `cep` e `phone`.
- Para criar um administrador, envie `name`, `email`, `password` e `is_barber`. O campo `phone` também é necessário quando `is_barber` for `true`.
- Na criação de agendamentos, clientes informam `barber_id`; o cliente associado é identificado pelo usuário autenticado. Administradores informam `client_id` e, quando não possuem perfil de barbeiro, também `barber_id`.
- As datas de início e fim do agendamento devem seguir o formato `Y-m-d H:i:s` e representar um período futuro.
- A listagem de agendamentos aceita os filtros `start_date`, `end_date`, `barber_id` e `client_id` pela query string.
- A criação e a alteração verificam a disponibilidade do cliente e do barbeiro; períodos sobrepostos são recusados.
- Os agendamentos têm duração máxima de 90 minutos. A alteração ou o cancelamento são bloqueados quando faltam 90 minutos ou menos para o início.

## Testando a API com Postman ou semelhantes

Antes de iniciar os testes, confirme que a API está rodando, que o banco foi populado com `php artisan migrate --seed` e que o worker de filas está ativo. Nos exemplos abaixo, considere a URL base `http://127.0.0.1:8000` e envie os corpos das requisições no formato JSON.

Nas requisições protegidas, utilize o Bearer Token retornado pelo login. Para facilitar a sequência, copie o valor de `bearerToken` da resposta de autenticação e utilize-o nas próximas chamadas.

### 1. Fluxo de autenticação e cadastro de cliente

1. **Cadastrar cliente — `POST /api/auth/register`**

   Envie os dados do novo cliente:

   ```json
   {
     "name": "Cliente Teste",
     "email": "cliente.teste@example.com",
     "password": "MinhaSenha@123",
     "cep": "01001000",
     "phone": "11987654321"
   }
   ```

   O CEP deve existir no ViaCEP. Após o cadastro, a aplicação agenda o envio do e-mail de verificação.

2. **Verificar e-mail — `GET /api/auth/email/verify/{id}/{hash}`**

   Acesse o link assinado recebido no e-mail para confirmar o endereço. O worker da fila `verification` precisa estar em execução para processar o envio.

3. **Fazer login — `POST /api/auth/login`**

   Após verificar o e-mail, envie:

   ```json
   {
     "email": "cliente.teste@example.com",
     "password": "MinhaSenha@123"
   }
   ```

   Copie o `bearerToken` retornado. Ele será utilizado nas próximas requisições do cliente.

4. **Consultar o próprio perfil — `GET /api/client`**

   Envie o Bearer Token do cliente. A resposta permite conferir os dados e identificar o `id` do cliente.

5. **Atualizar o cliente — `PATCH /api/client/{id}`**

   Utilize o identificador do cliente e envie os campos que deseja atualizar, conforme as validações da API.

6. **Excluir o cliente — `DELETE /api/client/{id}`**

   Utilize o mesmo identificador. Faça esta chamada apenas ao final dos testes, pois a operação exclui o cadastro.

### 2. Fluxo de administradores

Para testar as rotas administrativas, autentique-se com o administrador criado pelo seeder `UsersSeeder.php`.

1. **Fazer login como administrador — `POST /api/auth/login`**

   Envie o e-mail e a senha definidos no seeder. Copie o `bearerToken` retornado e utilize-o nas requisições administrativas.

2. **Listar administradores — `GET /api/admin`**

   Retorna a listagem paginada de administradores.

3. **Consultar administrador — `GET /api/admin/{id}`**

   Informe o identificador de um administrador existente.

4. **Cadastrar administrador — `POST /api/admin`**

   Envie `name`, `email`, `password` e `is_barber`. Para cadastrar um administrador sem perfil de barbeiro, utilize `is_barber` como `false`.

5. **Atualizar administrador autenticado — `PATCH /api/admin`**

   Envie os campos que deseja alterar para atualizar os dados do administrador que está autenticado.

6. **Excluir administrador autenticado — `DELETE /api/admin`**

   A exclusão é realizada para o administrador autenticado. A API impede a exclusão quando ele é o único administrador cadastrado; execute esta chamada somente ao final dos testes.

### 3. Fluxo de barbeiros

No projeto, o perfil de barbeiro é associado a um administrador e é criado pelo próprio endpoint de administração. Não há um conjunto separado de rotas CRUD para barbeiros.

1. **Cadastrar administrador com perfil de barbeiro — `POST /api/admin`**

   Envie `is_barber` como `true` e informe também o telefone:

   ```json
   {
     "name": "Barbeiro Teste",
     "email": "barbeiro.teste@example.com",
     "password": "MinhaSenha@123",
     "is_barber": true,
     "phone": "11987654321"
   }
   ```

2. **Identificar o barbeiro**

   Utilize o identificador do perfil de barbeiro associado ao administrador criado. Esse identificador será informado no campo `barber_id` ao criar ou filtrar agendamentos.

### 4. Fluxo de clientes pelo administrador

Com o token do administrador:

1. **Listar clientes — `GET /api/client`**. A listagem retorna os clientes cadastrados.
2. **Consultar cliente — `GET /api/client/{id}`**. Informe o ID de um cliente existente.
3. **Atualizar cliente — `PATCH /api/client/{id}`**. Envie os campos que deseja modificar, respeitando as validações da API.
4. **Excluir cliente — `DELETE /api/client/{id}`**. Utilize o ID do cliente e execute a operação apenas quando não precisar mais dele nos testes.

O próprio cliente também pode consultar, atualizar e excluir seu cadastro, respeitando as permissões de proprietário.

### 5. Fluxo de agendamentos

O agendamento relaciona um cliente a um barbeiro. Antes de criá-lo, tenha um cliente e um barbeiro cadastrados e verifique seus respectivos IDs.

1. **Criar agendamento como cliente — `POST /api/scheduling`**

   Com o token de um cliente, envie o barbeiro e o período desejado. O cliente do agendamento é identificado pela autenticação:

   ```json
   {
     "barber_id": 2,
     "start_date": "2026-11-02 14:00:00",
     "end_date": "2026-11-02 15:30:00"
   }
   ```

2. **Criar agendamento como administrador — `POST /api/scheduling`**

   Com o token de administrador, informe o cliente. Caso o administrador não possua perfil de barbeiro, informe também o `barber_id`:

   ```json
   {
     "client_id": 1,
     "barber_id": 2,
     "start_date": "2026-11-02 14:00:00",
     "end_date": "2026-11-02 15:30:00"
   }
   ```

3. **Listar agendamentos — `GET /api/scheduling`**

   Consulte os agendamentos acessíveis ao usuário autenticado. É possível combinar os filtros abaixo na query string:

   - `start_date`: data inicial;
   - `end_date`: data final;
   - `barber_id`: identificador do barbeiro;
   - `client_id`: identificador do cliente.

   Exemplo de consulta:

   ```text
   GET http://127.0.0.1:8000/api/scheduling?start_date=2026-11-01&end_date=2026-11-30&barber_id=2
   ```

4. **Consultar agendamento — `GET /api/scheduling/{id}`**

   Informe o ID do agendamento. A consulta é permitida ao administrador ou ao cliente responsável pelo agendamento.

5. **Atualizar agendamento — `PATCH /api/scheduling/{id}`**

   Informe o ID e os campos que deseja alterar, utilizando o formato de data `Y-m-d H:i:s`. A API valida novamente a disponibilidade do cliente e do barbeiro.

6. **Cancelar agendamento — `DELETE /api/scheduling/{id}`**

   Informe o ID do agendamento. A operação está sujeita às regras de permissão e ao prazo mínimo de 90 minutos antes do início.

   A API não permite períodos sobrepostos e limita a duração de cada agendamento a 90 minutos. As datas informadas devem estar no futuro.

### 6. Encerrar sessão

**Logout — `POST /api/auth/logout`**

Envie a requisição com o Bearer Token utilizado na sessão. O token atual será invalidado.

## Requisitos Técnicos Atendidos

### Setup

- [x] Criação do projeto utilizando Laravel 12.
- [x] Configuração da conexão com banco de dados MySQL.
- [x] Instalação e configuração do Laravel Sanctum para autenticação da API.
- [x] Configuração do envio de e-mails via SMTP do Laravel.
- [x] Documentação da API utilizando Swagger/OpenAPI e Apidog.

### API (Endpoints)

- [x] Proteção dos endpoints de clientes e agendamentos por autenticação via token.
- [x] Proteção das rotas de acordo com os tipos de usuários.
- [x] Envio de e-mail de notificação para todos os administradores após a criação de um novo agendamento.
- [x] Conteúdo do e-mail contendo as informações do novo agendamento, incluindo nome do cliente, data e horário do serviço.

### Estrutura do Banco de Dados

- [x] Criação das migrations e models correspondentes às entidades do sistema:
  - `user_types`: armazenamento dos tipos de usuários.
  - `users`: gerenciamento das contas de acesso, contendo nome, e-mail, senha e tipo de usuário.
  - `clients`: armazenamento dos dados dos clientes, com relacionamento com a tabela `users`.
  - `scheduling`: registro dos agendamentos, com relacionamento com os clientes e campos de data de início e término.
- [x] Configuração dos relacionamentos entre Models e Controllers, garantindo a integridade e comunicação entre as entidades (por exemplo, um agendamento pertence a um cliente).

## Bônus Implementados

- [x] Utilização de Service Classes para separar a lógica de negócio dos Controllers.
- [x] Implementação de paginação na listagem de clientes e agendamentos.
- [x] Implementação de filtros por data e por cliente nos agendamentos.
- [x] Utilização de FormRequest para validação robusta dos dados recebidos pela API.
- [x] Implementação de filas para o processamento assíncrono do envio de e-mails.
- [x] Utilização de UUIDs.

## Estrutura geral do projeto

Abaixo estão os principais diretórios e arquivos relacionados à API:

```text
Teste-Next_Tecnologia/
├── app/                              # Código principal da aplicação
│   ├── Exceptions/                   # Tratamento de exceções personalizadas
|   |              
│   ├── Http/
│   │   ├── Controllers/              # Controllers e endpoints da API
│   │   ├── Middleware/               # Controle de acesso e permissões
│   │   ├── Requests/                 # Validação das requisições
│   │   └── Resources/                # Padronização das respostas JSON
|   |   
│   ├── Jobs/                         # Processamento assíncrono de tarefas
│   ├── Models/                       # Models e relacionamentos do banco
│   ├── Notifications/                # Notificações por e-mail
│   └── Services/                     # Regras de negócio da aplicação
├── bootstrap/                        # Inicialização e configuração da aplicação
├── config/                           # Configurações dos serviços
├── database/
│   ├── migrations/                   # Estrutura e criação das tabelas
│   └── seeders/                      # Dados iniciais para execução
├── public/                           # Diretório público da aplicação
|
├── routes/
│   └── api.php                       # Definição das rotas e endpoints da API
├── storage/
│   └── api-docs/                     # Documentação OpenAPI gerada
```

---

Desenvolvido como parte do teste técnico para estágio Backend — Next Tecnologia.