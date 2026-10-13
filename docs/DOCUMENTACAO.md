# Documentação do Projeto - Lista de Presentes

## 1. Visão Geral do Projeto

Este é um sistema web construído com **Laravel** (backend) e **Inertia.js/Vue.js** (frontend), funcionando como uma plataforma de gerenciamento de listas de presentes. O projeto permite que usuários criem e gerenciem listas de presentes, convidem amigos, organizem eventos e participem de grupos sociais.

### Tecnologias Utilizadas
- **Backend**: Laravel 11
- **Frontend**: Vue.js 3 + Inertia.js
- **Database**: SQLite
- **Auth**: Laravel Breeze
- **Estilização**: Tailwind CSS

---

## 2. Estrutura do Banco de Dados

### Entidades Principais

| Modelo | Tabela | Descrição |
|--------|--------|-----------|
| User | users | Usuários do sistema |
| Perfil | perfils | Informações adicionais do usuário (bio, avatar, telefone) |
| Lista | listas | Listas de presentes |
| Presente | presentes | Itens dentro das listas |
| Categoria | categorias | Categorias para organizar presentes |
| Grupo | grupos | Grupos sociais entre usuários |
| Amizade | amizades | Sistema de amizade entre usuários |

### Relacionamentos

```
User (1) -----> (1) Perfil
User (N) -----> (N) Lista (via lista_usuarios)
User (N) -----> (N) Presente
User (N) -----> (N) Categoria
User (N) <---> (N) Grupo (via grupo_usuario)
User (N) <---> (N) Amizade

Lista (1) -----> (N) Presente
Lista (N) -----> (1) Grupo
Lista (N) <---> (N) User

Presente (N) <---> (N) Categoria (via categoria_presente)
```

---

## 3. Funcionalidades

### 3.1 Autenticação
- Registro de novos usuários
- Login com email/senha
- Verificação de email
- Redefinição de senha
- Logout

### 3.2 Perfil
- Editar informações pessoais
- Adicionar biografia
- Upload de avatar
- Adicionar telefone
- Exclusão de conta

### 3.3 Listas de Presentes
- **Criar**: Nome, descrição, visibilidade, data do evento, imagem de capa, grupo
- **Editar**: Modificar todos os campos
- **Visualizar**: Ver detalhes e presentes
- **Excluir**: Remover lista
- **Compartilhar**: Adicionar usuários à lista
- **Associar a grupo**: Vincular lista a um grupo

### 3.4 Presentes
- **Criar**: Nome, descrição, preço, link, anotação, categorias
- **Editar**: Modificar todos os campos
- **Visualizar**: Ver detalhes do presente
- **Excluir**: Remover presente da lista
- **Status**: Marcar como comprado
- **Avaliação**: Sistema de nota (1-5)

### 3.5 Categorias
- Criar categorias personalizadas
- Editar nome das categorias
- Excluir categorias
- Associar categorias aos presentes

### 3.6 Grupos
- Criar grupos com nome e descrição
- Adicionar membros ao grupo
- Remover membros do grupo
- Editar informações do grupo
- Excluir grupo
- Associar listas ao grupo

### 3.7 Sistema de Amizades
- **Enviar solicitação**: Procurar usuário e enviar pedido
- **Aceitar/Rejeitar**: Responder solicitações pendentes
- **Visualizar amigos**: Lista de amigos ativos
- **Ver perfil do amigo**: Ver listas públicas e grupos em comum
- **Encerrar amizade**: Remover amizade

### 3.8 Dashboard
- Resumo de listas ativas
- Solicitações de amizade pendentes
- Grupos recentes
- Atalhos para funcionalidades principais

---

## 4. Estrutura de Arquivos

### Backend (app/)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── ListaController.php
│   │   ├── PresenteController.php
│   │   ├── CategoriaController.php
│   │   ├── GrupoController.php
│   │   ├── AmizadeController.php
│   │   ├── PerfilController.php
│   │   └── Auth/ (Breeze controllers)
│   ├── Requests/ (Validadores)
│   │   ├── ListaStoreValidator.php
│   │   ├── ListaUpdateValidator.php
│   │   ├── PresenteValidator.php
│   │   ├── PresenteUpdateValidator.php
│   │   ├── GrupoValidator.php
│   │   ├── CategoriaValidator.php
│   │   └── PerfilValidator.php
│   └── Middleware/
│       └── HandleInertiaRequests.php
├── Models/
│   ├── User.php
│   ├── Perfil.php
│   ├── Lista.php
│   ├── Presente.php
│   ├── Categoria.php
│   ├── Grupo.php
│   └── Amizade.php
├── Services/
│   └── FileUploadService.php
├── Traits/
│   └── HasUuid.php
└── Providers/
    └── AppServiceProvider.php
```

### Frontend (resources/js/)

```
resources/js/
├── Pages/
│   ├── Dashboard.vue
│   ├── Auth/ (Login, Register, etc.)
│   ├── Lista/ (Index, Show, Create, Edit)
│   ├── Presente/ (Create, Edit)
│   ├── Categoria/ (Index, Create, Edit)
│   ├── Grupo/ (Index, Show, Create, Edit)
│   ├── Amizade/ (Index, Show)
│   └── Profile/ (Edit)
├── Layouts/
│   ├── AppLayout.vue
│   ├── AuthenticatedLayout.vue
│   ├── GuestLayout.vue
│   ├── GridListas.vue
│   ├── GridListasGrupo.vue
│   ├── GridPresentes.vue
│   ├── GridAmizades.vue
│   └── GridGrupos.vue
└── Components/
    ├── Alert.vue
    ├── BotaoVoltar.vue
    ├── ConfirmDeleteModal.vue
    ├── InputLabel.vue
    ├── PhoneInput.vue
    ├── ShowPresente.vue
    ├── UserSearchBar.vue
    └── Componentes base (Button, Input, etc.)
```

---

## 5. Rotas

### Rotas Web Principais

| Método | Rota | Controller | Descrição |
|--------|------|------------|-----------|
| GET | /dashboard | DashboardController | Página inicial |
| GET/POST | /perfil | PerfilController | Editar perfil |
| GET | /amizades | AmizadeController | Listar amizades |
| POST | /amizades/{id} | AmizadeController | Enviar pedido |
| PUT | /amizades/{id} | AmizadeController | Aceitar pedido |
| DELETE | /amizades/{id} | AmizadeController | Remover amizade |
| GET | /api/usuarios/buscar | AmizadeController | Buscar usuários |
| GET/POST | /listas | ListaController | CRUD Listas |
| GET | /listas/{id} | ListaController | Ver lista |
| GET/POST | /presentes/create/{lista_id} | PresenteController | Criar presente |
| GET/POST/PUT/DELETE | /presentes | PresenteController | CRUD Presentes |
| GET/POST | /categorias | CategoriaController | CRUD Categorias |
| GET/POST | /grupos | GrupoController | CRUD Grupos |

---

## 6. Models - Métodos Principais

### User
```php
$user->perfil()          // HasOne - Perfil do usuário
$user->listas()         // BelongsToMany - Listas que participa
$user->presentes()      // HasMany - Presentes criados
$user->categorias()    // HasMany - Categorias criadas
$user->grupos()         // BelongsToMany - Grupos que participa
$user->amizades()       // HasMany - Todas as amizades
$user->amizadesAtivas() // HasMany - Amizades aceitas
$user->amizadesPendentes() // HasMany - Pedidos recebidos
```

### Lista
```php
$lista->cadastradoPor() // BelongsTo - Quem criou
$lista->usuarios()     // BelongsToMany - Participantes
$lista->presentes()    // HasMany - Presentes da lista
$lista->grupo()        // BelongsTo - Grupo associado
```

### Presente
```php
$presente->cadastradoPor() // BelongsTo - Quem criou
$presente->lista()         // BelongsTo - Lista pertence
$presente->categorias()   // BelongsToMany - Categorias
```

### Amizade
```php
Amizade::entre($userId1, $userId2) // Scope para buscar amizade entre dois usuários
```

---

## 7. Services

### FileUploadService
Responsável pelo upload de imagens:
- Validação de extensões permitidas (jpg, png, gif, webp)
- Geração de nome único com UUID
- Armazenamento em pasta pública
- Retorno da URL pública do arquivo

---

## 8. Traits

### HasUuid
Adiciona automaticamente UUIDs aos modelos:
- Gera UUID v4 automaticamente na criação
- Usa UUID como chave primária (tipo string)

---

## 9. Validadores

| Validador | Campos Validado |
|-----------|----------------|
| ListaStoreValidator | nome (obrigatório), descricao, status, visibilidade, data_evento, grupo_id, image_url |
| ListaUpdateValidator | Mesmo que store + id |
| PresenteValidator | nome (obrigatório), descricao, preco, link, lista_id |
| PresenteUpdateValidator | Mesmo que PresenteValidator + comprado, avaliacao |
| GrupoValidator | nome (obrigatório), descricao |
| CategoriaValidator | nome (obrigatório) |
| PerfilValidator | name, bio, avatar, telefone |

---

## 10. Executando o Projeto

### Pré-requisitos
- PHP 8.2+
- Composer
- Node.js 18+
- SQLite

### Instalação

```bash
# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Start development server
php artisan serve
```

### Compilar assets
```bash
npm run dev
```

---

## 11. Status do Projeto

### ✅ Implementado
- Sistema de autenticação completo (Laravel Breeze)
- CRUD completo de Listas de Presentes
- CRUD completo de Presentes
- CRUD completo de Categorias
- CRUD completo de Grupos
- Sistema de Amizades (enviar, aceitar, remover)
- Dashboard com resumos
- Edição de perfil
- Upload de imagens
- Interface responsiva com Vue.js

### 📝 Possíveis Melhorias Futuras
- Notificações em tempo real
- Sistema de mensagens entre usuários
- Integração com APIs de produtos
- Compartilhamento via link público
- Histórico de presentes dados
- Estatísticas e relatórios