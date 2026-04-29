# Plano de Implementação: Melhorias de Registro

## Visão Geral

Este documento detalha o plano de implementação para as 5 funcionalidades de melhoria no sistema de registro de usuários.

---

## 1. Validação de Username Único ✅ IMPLEMENTADO

### Descrição
Verificar se o username já existe no banco de dados em tempo real (via API) quando o usuário digitar o username.

### Implementação

#### Backend
- Criar rota API: `GET /api/check-username`
- Controller: `app/Http/Controllers/Api/UsernameController.php`
- Retorna JSON: `{ available: true/false, message: '...' }`

#### Frontend
- Adicionado listener no campo username (blur e input com debounce 500ms)
- Exibe feedback visual (spinner enquanto verifica, check verde ou X vermelho)
- Valida antes de submeter o formulário

---

## 2. Termos de Uso e Política de Privacidade ✅ IMPLEMENTADO

### Descrição
Adicionar checkbox obrigatória para aceitar termos de uso e política de privacidade antes do registro.

### Implementação

#### Backend
- Adicionada validação no `RegisteredUserController`:
  ```php
  'termos' => 'required|accepted',
  ```

#### Frontend
- Adicionado checkbox no formulário de registro
- Links para "/termos" e "/privacidade" (ainda não existem, criar depois)
- Exibe erro de validação se não marcado

---

## 3. Confirmação de Senha com Indicador de Força ✅ IMPLEMENTADO

### Descrição
Mostrar em tempo real a força da senha (fraca/média/forte) conforme o usuário digita.

### Implementação

#### Backend
- Melhorada validação de senha:
  ```php
  'password' => ['required', 'confirmed', Rules\Password::defaults()->mixedCase()->numbers()->symbols()]
  ```

#### Frontend
- Criado componente `PasswordStrength.vue`
- Calcula força baseada em:
  - Comprimento (8+ caracteres = +1, 12+ = +1)
  - Letras minúsculas (+1)
  - Letras maiúsculas (+1)
  - Números (+1)
  - Símbolos especiais (+1)
- Exibe barra de progresso colorida (vermelho/amarelo/verde)
- Exibe texto: "Fraca", "Média", "Forte"

---

## 4. CAPTCHA Anti-bot (hCaptcha) ⏳ PENDENTE

### Descrição
Adicionar verificação CAPTCHA para evitar registros automatizados por bots.

### Implementação

#### Pré-requisitos
1. Registrar em https://www.hcaptcha.com/
2. Obter Site Key e Secret Key
3. Adicionar ao `.env`:
   ```
   HCAPTCHA_SITE_KEY=xxx
   HCAPTCHA_SECRET_KEY=xxx
   ```

#### Backend
1. Opcional:Instalar package `composer require hcaptcha/hcaptcha`
2. Validar no controller:
   ```php
   'h-captcha-response' => 'required|captcha',
   ```

#### Frontend
1. Adicionar script do hCaptcha no layout
2. Adicionar widget no formulário de registro
3. Enviar token no form data

---

## 5. Verificação de Email Obrigatória ✅ IMPLEMENTADO

### Descrição
Enviar email de confirmação com link para ativar a conta. Usuário só pode acessar após verificar o email.

### Implementação

#### Backend
1. **Model User** - Implementar `MustVerifyEmail`:
   ```php
   use Illuminate\Contracts\Auth\MustVerifyEmail;
   class User extends Authenticatable implements MustVerifyEmail
   ```

2. **RegisteredUserController** - Alterar fluxo:
   - NÃO fazer login automático após registro
   - Redirecionar para página "aguarde verificação" (`verification.pending`)
   - Usuário recebe email de verificação automaticamente

3. **AuthenticatedSessionController** - Verificação no login:
   - Se email não verificado, redireciona para página de verificação

4. **Rotas** - Nova rota:
   - `/verify-email/pending` - Página de espera com opção de reenviar email

#### Frontend
- Criar página `VerificationPending.vue`
- Mostrar instruções e botão "reenviar email"
- Link de logout para caso precise usar outra conta

---

## Arquivos Modificados

### Backend
- `app/Models/User.php` - Adicionado MustVerifyEmail
- `app/Http/Controllers/Auth/RegisteredUserController.php` - Validações e fluxo alterado
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` - Verificação no login
- `app/Http/Controllers/Api/UsernameController.php` - NOVO
- `app/Http/Controllers/Auth/EmailVerificationPendingController.php` - NOVO
- `app/Http/Controllers/EmailVerifiedCheckController.php` - NOVO
- `routes/api.php` - NOVO
- `routes/auth.php` - Adicionada rota de verificação pendente
- `routes/web.php` - Adicionada rota /home
- `bootstrap/app.php` - Adicionado suporte a API

### Frontend
- `resources/js/Pages/Auth/Register.vue` - Username validation, termos checkbox
- `resources/js/Components/PasswordStrength.vue` - NOVO
- `resources/js/Pages/Auth/VerificationPending.vue` - NOVO

---

## Testes Recomendados

1. ✅ Registrar com username existente (deve falhar)
2. ✅ Registrar com username disponível (deve funcionar)
3. ✅ Registrar sem aceitar termos (deve falhar)
4. ✅ Registrar com senha fraca (mostrar warning)
5. ✅ Registrar com senha forte (mostrar OK)
6. ⏳ Registrar com CAPTCHA inválido (deve falhar)
7. ✅ Registrar novo usuário (receber email)
8. ⏳ Clicar no link do email (conta verificada)
9. ✅ Tentar login sem verificar email (bloqueado)

---

## Notas

- **Email**: O sistema está configurado para usar driver `log` (escreve no arquivo de log). Para produção, configurar SMTP no `.env`.
- **CAPTCHA**: Deixado para implementação futura, pois requer conta externa no hCaptcha.
- **Termos**: Os links "/termos" e "/privacidade" ainda não existem - criar páginas simples depois.