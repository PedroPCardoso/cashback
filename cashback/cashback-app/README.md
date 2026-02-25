# 💰 Sistema de Gestão de Despesas e Cashback

Um sistema moderno e robusto para gestão de finanças pessoais, projetado para categorizar despesas automaticamente e calcular o saldo de cashback acumulado a cada mês, com base em regras configuráveis por categoria.

## 🏗️ Arquitetura e Engenharia

Este projeto foi construído utilizando **Domain-Driven Design (DDD)** em harmonia com o framework **Laravel 12**. A aplicação é estritamente dividida em camadas para garantir baixo acoplamento, alta coesão e facilidade de testes automatizados:

- **Domain Layer:** Contém as regras de negócio puras (Entidades, Value Objects, Contratos de Repositórios e Serviços de Domínio). Não possui dependências do framework.
- **Application Layer:** Contém os Casos de Uso (Use Cases) que orquestram as transições de estado e interações entre o domínio e a infraestrutura.
- **Infrastructure Layer:** Implementações técnicas, como Repositórios baseados no Eloquent ORM (Banco de Dados) e infraestruturas do Laravel.
- **Presentation Layer:** Controladores RESTful API, Views Blade e recursos Web para a interface do usuário.

## 🚀 Funcionalidades Principais

- **📊 Dashboard de Resumo Mensal:** Visualize o total gasto e o cashback acumulado por categoria em um mês específico em uma interface moderna e com Dark Theme nativo.
- **🤖 Categorização Automática:** Defina regras com palavras-chave. As novas transações inseridas (via Web ou API) são automaticamente analisadas e associadas à categoria correta de acordo com a prioridade das regras.
- **💸 Cálculo de Cashback Inteligente:** Cada categoria possui um limite financeiro mensal e uma taxa fixa de cashback (ex: 5% até R$500). O sistema calcula pro-rata o cashback caso a transação cruze exata e perfeitamente o limite parametrizado.
- **📝 Gestão Completa de Transações:** Registre novas despesas ou edite/exclua os registros antigos. Qualquer alteração dispara a refatoração automática em tempo real dos limites consumidos nas categorias do mês pertinente, garantindo o saldo sempre exato.
- **⚙️ Configuração Dinâmica de Categorias:** Ajuste livremente as regras de auto-categorização, crie novas gavetas de gastos com novos percentuais de retorno conforme suas regras particulares.

## 💻 Stack Tecnológica

- **Backend:** PHP 8.3 e Laravel 12
- **Banco de Dados:** PostgreSQL 16
- **Cache & Sessão:** Redis 7
- **Frontend / UI:** Views Blade renderizadas no servidor com Design Premium Responsivo
- **Infraestrutura:** Ambiente totalmente isolado usando Docker & Docker Compose
- **Qualidade de Código Strict:** PHPStan (Level 8 target), PHPUnit e Laravel Pint (PSR-12 ruleset)

## 🛠️ Instalação e Execução Local

O projeto foi totalmente "dockerizado" para que você suba o ambiente em poucos minutos sem configurar o PHP na sua máquina hospedeira.

### Pré-requisitos
- Docker e Docker Compose instalados.

### Passo-a-passo

1. Clone este repositório e acesse a raiz web (`cashback-app`):
   ```bash
   cd cashback-app
   ```
2. Copie os parâmetros do ambiente configurados no exemplo:
   ```bash
   cp .env.example .env
   ```
3. Suba o stack infraestrutural via Docker:
   ```bash
   docker compose build
   docker compose up -d
   ```
4. Acesse o bash do seu novo contêiner web para os comandos internos:
   ```bash
   docker compose exec -u www-data app bash
   ```
5. Por fim, dentro do contêiner instale as dependências e o banco de dados:
   ```bash
   composer install
   php artisan key:generate
   php artisan migrate:fresh --seed
   ```
   
Feito! O framework e o Web Server subirão limpos. Acesse a aplicação completa via navegador na rota principal pelo link **`http://localhost:8080`**.

## 🧪 Testes e Extrema Estabilidade (Quality Gates)

O núcleo da aplicação reflete um sistema transacional que cuida de cálculos financeiros. Para garantir pureza do código, o sistema é amparado por testes ferrenhos.

Para rodar a suíte completa com as dezenas de testes end-to-end integrados e limpos:
```bash
docker compose exec -u www-data app php artisan test
```

Auditoria estática e caça preventiva de bugs (Zero Errors Allowed no Level 8):
```bash
docker compose exec -u www-data app ./vendor/bin/phpstan analyse --memory-limit=1G
```

Para aplicar automaticamente as correções de indentação e standard do PHP:
```bash
docker compose exec -u www-data app ./vendor/bin/pint
```
