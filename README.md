# 💰 Sistema de Gerenciamento Financeiro

Um sistema completo de gerenciamento financeiro pessoal desenvolvido em Laravel, com interface responsiva e funcionalidades avançadas para controle de receitas, despesas, categorias, metas e relatórios.

## ✨ Funcionalidades

- 🔐 **Autenticação Segura** - Sistema de login e registro com validação
- 📊 **Dashboard Interativo** - Visão geral das finanças com gráficos e resumos
- 💳 **Gestão de Transações** - Registro e controle de receitas e despesas
- 🏷️ **Categorização** - Organização por categorias personalizáveis
- 🎯 **Metas Financeiras** - Definição e acompanhamento de objetivos
- 📈 **Relatórios Detalhados** - Análises com filtros e exportação
- 📱 **Interface Responsiva** - Funciona perfeitamente em dispositivos móveis
- ⚡ **Validações em Tempo Real** - Feedback instantâneo nos formulários

## 🚀 Instalação e Configuração

### Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- NPM ou Yarn

### Passos de Instalação

1. **Clone o repositório**
```bash
git clone <repository-url>
cd sistema-financeiro
```

2. **Instale as dependências PHP**
```bash
composer install
```

3. **Instale as dependências JavaScript**
```bash
npm install
```

4. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados**
```bash
# Edite o arquivo .env com suas configurações de banco
# Para desenvolvimento, você pode usar SQLite:
touch database/database.sqlite
```

6. **Execute as migrações**
```bash
php artisan migrate
```

7. **Compile os assets**
```bash
npm run build
# ou para desenvolvimento:
npm run dev
```

8. **Popule com dados de demonstração**
```bash
php artisan demo:seed
# ou com dados de desenvolvimento:
php artisan demo:seed --dev
```

9. **Inicie o servidor**
```bash
php artisan serve
```

## 👤 Usuários de Demonstração

Após executar o seeder, você pode fazer login com:

| Usuário | Email | Senha |
|---------|-------|-------|
| Usuário Demo | demo@financeiro.com | demo123 |
| Administrador | admin@financeiro.com | admin123 |
| Test User | test@example.com | password |

## 🛠️ Tecnologias Utilizadas

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade Templates, Bootstrap 5, Chart.js
- **Banco de Dados**: SQLite (desenvolvimento), MySQL/PostgreSQL (produção)
- **Autenticação**: Laravel Breeze
- **Testes**: PHPUnit
- **Build**: Vite

## 📁 Estrutura do Projeto

```
app/
├── Actions/           # Lógica de negócio
├── Http/
│   ├── Controllers/   # Controladores
│   └── Requests/      # Validações de formulário
├── Models/            # Modelos Eloquent
├── Policies/          # Políticas de autorização
└── Observers/         # Observadores de modelo

resources/
├── views/
│   ├── components/    # Componentes Blade reutilizáveis
│   ├── layouts/       # Layouts base
│   └── [modules]/     # Views por módulo
├── css/               # Estilos CSS
└── js/                # JavaScript

database/
├── migrations/        # Migrações do banco
├── seeders/          # Seeders para dados de exemplo
└── factories/        # Factories para testes
```

## 🧪 Executando Testes

```bash
# Todos os testes
php artisan test

# Testes específicos
php artisan test --filter=TransactionTest

# Com cobertura
php artisan test --coverage
```

## 📊 Comandos Úteis

```bash
# Limpar dados e recriar com demo
php artisan migrate:fresh --seed

# Apenas dados de demo
php artisan demo:seed

# Dados de desenvolvimento
php artisan demo:seed --dev

# Limpar cache
php artisan optimize:clear
```

## 🔧 Desenvolvimento

### Adicionando Nova Funcionalidade

1. Crie a migração: `php artisan make:migration create_table`
2. Crie o modelo: `php artisan make:model Model`
3. Crie o controller: `php artisan make:controller ModelController --resource`
4. Crie as validações: `php artisan make:request StoreModelRequest`
5. Crie as actions: `php artisan make:class Actions/CreateModelAction`
6. Adicione as rotas em `routes/web.php`
7. Crie as views em `resources/views/models/`
8. Escreva os testes

### Padrões de Código

- Use **Actions** para lógica de negócio
- Use **Form Requests** para validação
- Use **Policies** para autorização
- Mantenha controllers enxutos (apenas coordenação)
- Escreva testes para toda nova funcionalidade

## 📝 Licença

Este projeto está licenciado sob a [MIT License](LICENSE).
