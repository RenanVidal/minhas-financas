# Documento de Design - Sistema de Gerenciamento Financeiro

## Visão Geral

O sistema de gerenciamento financeiro será desenvolvido utilizando Laravel 12 com arquitetura MVC, seguindo os padrões e convenções do framework. A aplicação utilizará autenticação nativa do Laravel (Laravel Breeze), Eloquent ORM para persistência de dados, e Blade para renderização das views. O sistema será responsivo e seguirá princípios de design limpo e experiência do usuário intuitiva.

**Decisões de Design Principais:**
- **Laravel Breeze**: Escolhido para autenticação por ser leve, bem documentado e seguir as melhores práticas do Laravel
- **Blade + Bootstrap 5**: Combinação que oferece desenvolvimento rápido com componentes responsivos
- **Chart.js**: Biblioteca JavaScript leve para gráficos interativos no dashboard
- **SQLite para desenvolvimento**: Facilita setup inicial e testes, com migração simples para MySQL/PostgreSQL em produção

## Arquitetura

### Estrutura Geral
- **Framework**: Laravel 12 com PHP 8.2+
- **Banco de Dados**: SQLite (desenvolvimento) / MySQL ou PostgreSQL (produção)
- **Frontend**: Blade Templates + Bootstrap 5 + Chart.js para gráficos
- **Autenticação**: Laravel Breeze ou sistema nativo do Laravel
- **Validação**: Form Requests do Laravel
- **Testes**: PHPUnit com Feature e Unit Tests

### Padrões Arquiteturais
- **Action Pattern**: Para toda lógica de negócio (cálculos, validações, operações complexas)
- **Form Requests**: Para validação centralizada de entrada
- **Resource Controllers**: Para operações CRUD padronizadas (apenas coordenação)
- **Middleware**: Para autenticação e autorização
- **Observer Pattern**: Para eventos automáticos (atualização de progresso de metas)
- **Single Responsibility**: Cada Action tem uma responsabilidade específica

## Componentes e Interfaces

### Actions (Lógica de Negócio)

```php
// Auth Actions
class RegisterUserAction
{
    // Registra novo usuário com validação de email único (Req. 1.2, 1.3)
    public function execute(array $data): User
}

class AuthenticateUserAction  
{
    // Autentica usuário com credenciais (Req. 1.5, 1.6)
    public function execute(string $email, string $password): bool
}

// Transaction Actions
class CreateTransactionAction
{
    // Cria transação e atualiza saldo automaticamente (Req. 3.3)
    public function execute(User $user, array $data): Transaction
}

class UpdateTransactionAction
{
    // Atualiza transação e recalcula saldo (Req. 3.4)
    public function execute(Transaction $transaction, array $data): Transaction
}

class DeleteTransactionAction
{
    // Remove transação e ajusta saldo (Req. 3.5)
    public function execute(Transaction $transaction): bool
}

// Category Actions
class DeleteCategoryAction
{
    // Verifica transações associadas antes de excluir (Req. 2.5)
    public function execute(Category $category): bool
}

// Dashboard Actions
class CalculateDashboardDataAction
{
    // Calcula saldo, receitas/despesas, últimas transações (Req. 4.1, 4.2)
    public function execute(User $user): array
}

class GenerateFinancialChartAction
{
    // Gera dados para gráfico de evolução (Req. 4.3)
    public function execute(User $user, int $months = 6): array
}

// Report Actions
class GenerateFinancialReportAction
{
    // Gera relatório com filtros aplicados (Req. 5.2, 5.3)
    public function execute(User $user, array $filters): Collection
}

class ExportReportAction
{
    // Exporta relatório em PDF ou Excel (Req. 5.4)
    public function execute(Collection $data, string $format): string
}

// Goal Actions
class CheckGoalProgressAction
{
    // Verifica progresso e atualiza status da meta (Req. 6.3)
    public function execute(Goal $goal): Goal
}

class CheckAchievedGoalsAction
{
    // Verifica metas atingidas e gera notificações (Req. 6.4)
    public function execute(User $user): Collection
}

class CheckExpiringGoalsAction
{
    // Verifica metas próximas do vencimento (Req. 6.5)
    public function execute(User $user): Collection
}
```

### Models e Relacionamentos

```php
// User (já existente, será estendido)
class User extends Authenticatable
{
    // Relacionamentos
    public function categories(): HasMany
    public function transactions(): HasMany  
    public function goals(): HasMany
}

// Category
class Category extends Model
{
    // Campos: name, description, type (income/expense), color, user_id
    public function user(): BelongsTo
    public function transactions(): HasMany
}

// Transaction
class Transaction extends Model
{
    // Campos: description, amount, date, type, category_id, user_id
    public function user(): BelongsTo
    public function category(): BelongsTo
}

// Goal
class Goal extends Model
{
    // Campos: name, target_amount, current_amount, deadline, category_id, user_id
    public function user(): BelongsTo
    public function category(): BelongsTo
}
```

### Controllers

```php
// AuthController (Laravel Breeze)
- register(): Exibe formulário de registro com nome, email e senha (Req. 1.1)
- store(): Processa registro, valida dados únicos e cria conta (Req. 1.2, 1.3)
- login(): Exibe formulário de login com email e senha (Req. 1.4)
- authenticate(): Valida credenciais e autentica usuário (Req. 1.5, 1.6)

// DashboardController
- index(): Exibe saldo atual, receitas/despesas do mês, últimas 5 transações, 
          gráfico de evolução (6 meses) e resumo por categorias (Req. 4.1-4.4)
- welcome(): Exibe mensagem orientativa quando não há transações (Req. 4.5)

// CategoryController (Resource Controller)
- index(): Lista todas as categorias do usuário (Req. 2.1)
- create(): Formulário com nome, descrição e tipo (receita/despesa) (Req. 2.2)
- store(): Salva nova categoria com validação (Req. 2.3)
- edit(): Formulário de edição de categoria existente (Req. 2.4)
- update(): Atualiza categoria com validação (Req. 2.4)
- destroy(): Remove categoria após verificar transações associadas e solicitar confirmação (Req. 2.5)

// TransactionController (Resource Controller)
- index(): Lista transações ordenadas por data com paginação (Req. 3.1)
- create(): Formulário com descrição, valor, data, categoria e tipo (Req. 3.2)
- store(): Salva transação, valida valor positivo e atualiza saldo (Req. 3.3, 3.6)
- edit(): Formulário de edição de transação (Req. 3.4)
- update(): Atualiza transação e recalcula saldo (Req. 3.4)
- destroy(): Remove transação e ajusta saldo (Req. 3.5)

// ReportController
- index(): Interface com filtros por período, categoria e tipo (Req. 5.1)
- generate(): Gera relatório com tabela de transações e totais por categoria/tipo (Req. 5.2, 5.3)
- export(): Exporta dados em PDF ou Excel (Req. 5.4)
- empty(): Exibe mensagem quando período não contém transações (Req. 5.5)

// GoalController (Resource Controller)
- index(): Lista metas com progresso atual e alertas de vencimento (Req. 6.1, 6.5)
- create(): Formulário para valor alvo, prazo e categoria (Req. 6.2)
- store(): Salva nova meta com validação (Req. 6.2)
- show(): Exibe progresso detalhado da meta (Req. 6.3)
- update(): Atualiza progresso da meta automaticamente
- destroy(): Remove meta
- checkAchievements(): Verifica metas atingidas e exibe notificações (Req. 6.4)
```

### Form Requests

```php
// RegisterRequest
- Validação: name (required, max:255), email (required, email, unique:users), password (required, min:8, confirmed)

// LoginRequest  
- Validação: email (required, email), password (required)

// StoreCategoryRequest
- Validação: name (required, max:255), description (nullable), type (required, in:income,expense)
- Regra customizada: verificar se categoria pertence ao usuário autenticado

// StoreTransactionRequest  
- Validação: description (required, max:255), amount (required, numeric, min:0.01), date (required, date), 
  category_id (required, exists:categories,id), type (required, in:income,expense)
- Regra customizada: verificar se categoria pertence ao usuário autenticado

// StoreGoalRequest
- Validação: name (required, max:255), target_amount (required, numeric, min:1), 
  deadline (required, date, after:today), category_id (nullable, exists:categories,id)
- Regra customizada: verificar se categoria pertence ao usuário autenticado
```

## Modelos de Dados

### Estrutura das Tabelas

```sql
-- users (já existe, pode precisar de ajustes)
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- categories
CREATE TABLE categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type ENUM('income', 'expense') NOT NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- transactions
CREATE TABLE transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    category_id BIGINT NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- goals
CREATE TABLE goals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    category_id BIGINT NULL,
    name VARCHAR(255) NOT NULL,
    target_amount DECIMAL(10,2) NOT NULL,
    current_amount DECIMAL(10,2) DEFAULT 0,
    deadline DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);
```

### Índices para Performance

```sql
-- Índices para otimização de consultas
CREATE INDEX idx_transactions_user_date ON transactions(user_id, date);
CREATE INDEX idx_transactions_category ON transactions(category_id);
CREATE INDEX idx_categories_user_type ON categories(user_id, type);
CREATE INDEX idx_goals_user_status ON goals(user_id, status);
```

## Tratamento de Erros

### Estratégias de Validação
- **Form Requests**: Validação centralizada com mensagens personalizadas em português
- **Model Validation**: Regras de negócio no nível do modelo (ex: não permitir exclusão de categoria com transações)
- **Database Constraints**: Integridade referencial e validações no banco

### Tratamento de Exceções
- **Handler Personalizado**: Captura e trata exceções específicas do domínio financeiro
- **Logs Estruturados**: Registro de operações críticas (criação/edição/exclusão de transações)
- **Mensagens de Erro**: Feedback claro para o usuário em português

### Validações de Negócio
- **Autenticação**: Verificar se usuário está autenticado para acessar qualquer funcionalidade
- **Autorização**: Verificar se categoria/transação/meta pertence ao usuário antes de operações
- **Integridade de Dados**: Validar se valor da transação é positivo (Req. 3.6)
- **Relacionamentos**: Impedir exclusão de categoria com transações associadas (Req. 2.5)
- **Unicidade**: Garantir que email seja único no registro (Req. 1.3)
- **Metas**: Validar que prazo da meta seja futuro (Req. 6.2)

## Estratégia de Testes

### Testes Unitários
- **Models**: Testes de relacionamentos, scopes e métodos customizados
- **Actions**: Testes de lógica de negócio, cálculos financeiros e regras específicas
- **Form Requests**: Testes de validação com dados válidos e inválidos

### Testes de Feature
- **Autenticação**: Registro com dados válidos/inválidos, login com credenciais corretas/incorretas, proteção de rotas (Req. 1)
- **Categorias**: CRUD completo, validação de exclusão com transações associadas (Req. 2)
- **Transações**: CRUD completo, validação de valores, atualização de saldo (Req. 3)
- **Dashboard**: Exibição de saldo, resumos, gráficos e mensagem para usuários novos (Req. 4)
- **Relatórios**: Geração com filtros, exportação e tratamento de períodos vazios (Req. 5)
- **Metas**: CRUD completo, cálculo de progresso, notificações e alertas (Req. 6)

### Testes de Integração
- **Database**: Testes com transações de banco e rollback
- **API Endpoints**: Testes de resposta HTTP e formato JSON
- **Middleware**: Testes de autenticação e autorização

### Dados de Teste
- **Factories**: Criação de dados fake para User, Category, Transaction, Goal
- **Seeders**: População inicial com categorias padrão e dados de exemplo
- **Database Refresh**: Limpeza entre testes para isolamento

### Cobertura de Testes
- Meta de 80%+ de cobertura de código
- Testes obrigatórios para lógica financeira crítica
- Testes de regressão para bugs corrigidos