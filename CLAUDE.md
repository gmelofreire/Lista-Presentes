# CLAUDE.md — Lista de Presentes

Aplicação web em português (pt-BR) para criar e compartilhar **listas de presentes**, organizar-se em **grupos**, fazer **amizades** entre usuários e receber **notificações**. Todo o domínio (tabelas, models, rotas, mensagens) é em português — mantenha esse padrão.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12, PHP ^8.2 |
| Frontend | Vue 3 (Composition API) + Inertia.js 2 (`inertiajs/inertia-laravel` + `@inertiajs/vue3`) |
| Rotas no JS | Ziggy (`route('nome')` disponível no front) |
| Estilo | Tailwind CSS 3 + `@tailwindcss/forms`, Headless UI, Heroicons |
| Build | Vite 7 + `laravel-vite-plugin` |
| Auth | Laravel Breeze (versão Vue/Inertia), customizado |
| Banco | SQLite por padrão (`.env.example`); o `.env` local aponta para outro driver (`DB_HOST` etc.) |
| Sessão/cache/fila | driver `database` |
| E-mail | `MAIL_MAILER=log` por padrão |
| Deploy | Heroku-like (`Procfile`: `heroku-php-apache2 public/`) |
| Libs front extras | `vue3-carousel`, `vue-the-mask`, `lodash`, `axios` |

Não há SPA separada: o Laravel serve `resources/views/app.blade.php` e o Inertia injeta a página Vue. Controllers retornam `Inertia::render('Pasta/Pagina', [...props])`.

## Comandos

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed        # UserSeeder cria usuários iniciais
php artisan storage:link          # uploads vão para o disco 'public'
npm run dev                       # Vite (dev)  |  npm run build  (produção)
php artisan serve                 # ou XAMPP servindo /public
php artisan test                  # PHPUnit (testes de Auth e Profile do Breeze)
vendor/bin/pint                   # formatação PHP
```

Observação: `AppServiceProvider` chama `URL::forceScheme('https')` — em ambiente local sem HTTPS os links gerados saem com `https://`.

## Estrutura

```
app/
  Http/Controllers/   Dashboard, Lista, Presente, Categoria, Grupo, Amizade,
                      Notificacao, Perfil, EmailVerifiedCheck, Api/Username, Auth/*
  Http/Requests/      Validators (ListaStore/Update, Presente/PresenteUpdate,
                      Grupo, Categoria, Perfil) + Auth/LoginRequest
  Http/Middleware/    HandleInertiaRequests (props compartilhadas), TrustProxies
  Models/             User, Perfil, Lista, ListaParticipante, Presente, Categoria,
                      Grupo, Amizade, Notificacao
  Events/Listeners/   NovaListaNoGrupo -> NotificarMembrosDoGrupo (e-mail)
  Mail/               NovaListaGrupoMailable (view resources/views/emails/)
  Services/           FileUploadService (upload para disco 'public' + listas de extensões)
  Traits/             HasUuid (PK uuid string gerada em creating)
resources/js/
  Pages/              Amizade, Auth, Categoria, Grupo, Lista (Create/Edit/Index/Preview/Show),
                      Notificacao, Presente, Profile, Dashboard.vue, Welcome.vue
  Components/         Modal, ImageCropper, PresenteForm, ShowPresente, UserSearchBar,
                      PasswordStrength, PhoneInput, ConfirmDeleteModal, inputs/botões...
  Layouts/            AuthenticatedLayout, GuestLayout, AppLayout e Grids
                      (GridListas, GridListasGrupo, GridPresentes, GridGrupos, GridAmizades)
routes/               web.php (app), auth.php (Breeze), api.php (/api/check-username)
docs/                 DOCUMENTACAO.md, TO-DO.md, ALTERACOES.md (documentação anterior)
PLANO_REGISTRO.md     plano do fluxo de registro
```

## Modelo de dados

Quase todas as PKs são **UUID** (trait `HasUuid`), exceto `Amizade` (sem PK, chave composta lógica `usuario_id`+`amigo_id`).

- **User** (`name, email, username, password, perfil_id`; implementa `MustVerifyEmail`; coluna `conta_ativa`). No evento `created` cria automaticamente um **Perfil**.
- **Perfil** (tabela `perfis`): `data_nascimento, genero, telefone, image_url, biografia, user_id`.
- **Lista**: `nome, descricao, cadastrado_por_id, status, visibilidade, data_evento, grupo_id, image_url, token_convite`. Relações: `usuarios` (pivot `lista_usuarios`), `participantes` (`ListaParticipante`), `presentes`, `grupo`.
- **ListaParticipante** (`lista_participantes`): `lista_id, usuario_id, papel` (`admin | editor | visualizador`).
- **Presente**: `nome, descricao, preco, link, image_url, anotacoes, cadastrado_por, comprado, avaliacao, lista_id`; N:N com **Categoria** (`categoria_presente`).
- **Categoria**: `nome, hex_cor, descricao, icone, parent_id` (hierarquia), `ordem`, `e_default`, `cadastrado_por`.
- **Grupo**: `nome, descricao, image_url, cadastrado_por, user_id` + banner/convite (migration `add_invite_banner_to_grupos`); membros via pivot `grupo_usuario` com `role` (`proprietario | admin | visualizador`).
- **Amizade**: `usuario_id` (solicitante), `amigo_id`, `status` (`pendente | aceito | bloqueado`); scope `entre($a,$b)`.
- **Notificacao** (`notificacoes`): `user_id, tipo, titulo, mensagem, link, lido, data_lido`.

## Funcionalidades

- **Autenticação**: registro com username único (`[a-zA-Z0-9_]`, 3–30), indicador de força de senha, termos de uso; login por **e-mail ou username**; bloqueio após 10 tentativas (throttle 15 min); recuperação de senha; verificação de e-mail (atualmente desabilitada — commit "Desabilitado verificação de email"; `/home` decide entre dashboard e página de pendência); endpoint `GET /api/check-username`.
- **Perfil** (`/perfil`): edita nome, e-mail, username, telefone, data de nascimento, gênero, biografia e avatar (com `ImageCropper`). "Excluir conta" na verdade **desativa** (`conta_ativa=false`); reativa ao fazer login de novo.
- **Listas** (`/listas`): CRUD com busca (nome/descrição), filtros (status, grupo) e ordenação (`nome, data_evento, created_at`). Só aparecem listas em que o usuário é participante. Capa opcional (padrão `/img/default_cover.png`). O criador vira participante `admin`. Se a lista pertence a um grupo, dispara `NovaListaNoGrupo` → e-mail para os demais membros.
- **Presentes** (`/presentes`): criados dentro de uma lista (`/presentes/create/{lista_id}`), com preço, link, imagem, anotações, categorias, status "comprado" e avaliação. **`POST /presentes/buscar-dados-url`** faz scraping da URL do produto (og:title, description, preço, og:image via XPath) para preencher o formulário.
- **Categorias** (`/categorias`): CRUD por usuário, cores/ícones, subcategorias e reordenação (`POST /categorias/reorder`).
- **Grupos** (`/grupos`): CRUD, imagem/banner, convidar amigos, remover membro, promover a admin, sair, link de convite por token, listas do grupo.
- **Amizades** (`/amizades`): buscar usuários (`/api/usuarios/buscar`), enviar/aceitar/rejeitar/cancelar solicitação, bloquear/desbloquear, desfazer amizade, ver perfil do amigo (listas e grupos em comum). Cria notificações.
- **Notificações** (`/notificacoes`): listagem, contador de não lidas, recentes, marcar uma/todas como lidas.
- **Dashboard** (`/dashboard`): resumo de listas, solicitações pendentes e grupos recentes.

## Convenções e armadilhas

- Props globais do Inertia (`HandleInertiaRequests::share`): `auth.user` já traz `perfil, amizades, grupos, categorias, presentes` — cuidado com peso/dados expostos ao adicionar relações.
- Updates de recurso com upload usam **`POST`** (`listas.update`, `grupos.update`, `presentes.update`) em vez de PUT, por causa de `multipart/form-data` no Inertia.
- Uploads: sempre via `FileUploadService::upload($file, 'pasta', $service->extensoesImagem)`; retorna a URL pública (`Storage::url`).
- Mensagens flash usam `->with('status', ...)`; erros `withErrors(['error' => ...])`.
- Autorização é feita manualmente nos controllers (verificação de `role`/`papel`/`cadastrado_por`); não há Policies. Vários endpoints usam `Model::find($id)` sem checar pertencimento — revise ao mexer em segurança.
- **Funcionalidades com código mas sem rota em `routes/web.php`**: `ListaController` (`buscarParticipantes`, `adicionarParticipante`, `removerParticipante`, `atualizarPapel`, `gerarLinkConvite`, `participar` — esta é referenciada por `route('listas.participar')`) e `GrupoController` (`generateInviteLink`, `joinByToken`). Registre as rotas antes de usá-las.
- Listener `NotificarMembrosDoGrupo` é registrado por auto-descoberta do Laravel (type-hint no `handle`), não em provider.
- `Amizade` não tem chave primária; use `where`/`update` por `usuario_id`+`amigo_id` (ou `Amizade::entre`).
- Testes existentes são só os do Breeze (Auth, Profile); o domínio da aplicação não tem cobertura.
- `docs/TO-DO.md` lista o que falta (2FA, login social, CAPTCHA etc.); `docs/ALTERACOES.md` é o histórico. Os arquivos `DOCUMENTACAO.md`/`TO-DO.md` da raiz foram movidos para `docs/`.
