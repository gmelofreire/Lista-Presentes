# To-Do: Funcionalidades Faltantes

## Autenticação

### Melhorias de Registro
- [x] Validação de username único
- [x] Verificação de email obrigatória (enviar email de confirmação)
- [x] Termos de uso e política de privacidade
- [x] Confirmação de senha com indicador de força
- [ ] CAPTCHA anti-bot no registro (pendente - requer conta externa)

### Melhorias de Login
- [ ] "Lembrar-me" com duração configurável (removido)
- [x] Login com username além do email
- [ ] Login com redes sociais (Google, Facebook)
- [x] Bloqueio de conta após tentativas falhas otimizado (10 tentativas, 15 min)
- [ ] Código de verificação em duas etapas (2FA)
- [ ] Login via código enviado por email/SMS

### Melhorias de Recuperação de Senha
- [x] Tempo limite para link de redefinição (15 minutos)
- [ ] Enviar código por SMS além do email
- [ ] Histórico de senhas usadas (evitar repetição)
- [ ] Verificação adicional antes de redefinir

### Melhorias de Segurança
- [ ] Sessões ativas (ver dispositivos conectados)
- [ ] Terminar todas as sessões
- [ ] Notificação de novo login
- [ ] Lista de IPs permitidos
- [ ] Autenticação biométrica (impressão digital, face)
- [ ] Token de acesso por API

### Melhorias de Conta
- [x] Desativar conta (em vez de excluir)
- [x] Editar email com verificação
- [x] Alterar username
- [ ] Exportar dados do usuário (GDPR)
- [ ] Escolher idioma e fuso horário

---

## Perfil

### Informações Básicas (Já Implementado)
- [x] Nome
- [x] Email
- [x] Username
- [x] Avatar/Foto de perfil

### Informações do Perfil (Já Implementado)
- [x] Data de nascimento
- [x] Gênero
- [x] Telefone
- [x] Biografia

### Melhorias de Dados Pessoais
- [x] Editar username com verificação de unicidade
- [x] Alterar email com confirmação do email atual
- [ ] Adicionar segundo email alternativo
- [ ] Adicionar telefone adicional (WhatsApp)
- [ ] Verificação de telefone por SMS
- [ ] Data de nascimento com calendário interativo
- [ ] Selecionar avatar de sistema (emoji/figura)
- [x] Cortar e redimensionar avatar upado

### Melhorias de Privacidade
- [ ] Tornar perfil privado/público
- [ ] Escolher o que outros usuários podem ver
- [ ] Ocultar data de aniversário
- [ ] Ocultar telefone dos outros usuários
- [ ] Configurar quem pode enviar mensagens
- [ ] Configurar quem pode ver listas

### Melhorias de Redes Sociais
- [ ] Conectar conta do Google
- [ ] Conectar conta do Facebook
- [ ] Conectar conta do Instagram
- [ ] Conectar conta do LinkedIn
- [ ] Adicionar link pessoal (website, blog)
- [ ] Mostrar redes sociais no perfil público

### Visualização do Perfil
- [ ] Preview de como o perfil aparece para outros
- [ ] Estatísticas do perfil (visualizações, cliques)
- [ ] Ver quem visitou o perfil
- [ ] Tema de capa/banner personalizado

### Funcionalidades Extras
- [ ] Adicionar banner/capa do perfil
- [ ] Galeria de fotos no perfil
- [ ] Vídeo de apresentação (intro)
- [ ] QR Code do perfil para compartilhamento
- [ ] Card virtual do perfil (vCard)
- [ ] Ver perfil como visitante (incognito)

---

## Listas de Presentes

### Funcionalidades (Já Implementado)
- [x] Criar lista com nome, descrição, data do evento
- [x] Definir visibilidade (pública/privada)
- [x] Upload de imagem de capa
- [x] Associar lista a um grupo
- [x] Adicionar participantes à lista
- [x] Listar presentes da lista
- [x] Editar lista
- [x] Excluir lista
- [x] Busca/filtro de listas

### Melhorias de Criação
- [ ] Templates prontos (casamento, aniversário, formatura, etc.)
- [ ] Criar lista a partir de modelo
- [ ] Clone de lista existente
- [ ] Criar múltiplas listas de uma vez
- [ ] Definir tema/cor da lista
- [ ] Adicionar local do evento (endereço completo)
- [ ] Adicionar link do evento (Google Calendar, etc.)
- [ ] Datas personalizadas (chegada, divulgação, etc.)

### Melhorias de Apresentação
- [x] Visualização em grid (cards)
- [x] Visualização em lista (tabela) com toggle
- [x] Ordenar por nome, data, ordem de criação
- [x] Filtrar por status (ativa, arquivada, concluída)
- [x] Filtrar por grupo
- [ ] Preview da lista como outros veem
- [ ] Slideshow de capas das listas

### Melhorias de Participantes
- [ ] Adicionar participante por username/email
- [ ] Convite por link compartilhável
- [ ] Permissões por participante (admin, editor, visualizador)
- [ ] Notificar participantes de mudanças
- [ ] Chat da lista entre participantes
- [ ] Ver quem visualizou a lista
- [ ] Rascunho de participação (sem login)

### Compartilhamento
- [ ] Link público único da lista
- [ ] Link com senha opcional
- [ ] QR Code da lista
- [ ] Compartilhar por WhatsApp, email, etc.
- [ ] Estatísticas de visualizações
- [ ] Permitir visita sem login
- [ ] Widget incorporável (embed)

### Melhorias de Status
- [ ] Status: Rascunho, Ativa, Concluída, Arquivada
- [ ] Arquivar lista automaticamente após data do evento
- [ ] Restaurar lista arquivada
- [ ] Duplicar lista
- [ ] Exportar lista (PDF, Excel)

### Funcionalidades Extras
- [ ] Contador regressivo para o evento
- [ ] Metas de presentes (quantidade mínima)
- [ ] Orçamento total da lista
- [ ] Lista de presentes mais/menos desejados
- [ ] Ranking de presentes mais reservados
- [ ] Timeline de alterações na lista
- [ ] Versionamento de alterações

---

## Presentes

### Funcionalidades (Já Implementado)
- [x] Criar presente com nome, descrição, preço, link
- [x] Upload de imagem do presente
- [x] Adicionar categorias ao presente
- [x] Anotações no presente
- [x] Marcar como comprado
- [x] Avaliação do presente (nota 1-5)
- [x] Editar presente
- [x] Excluir presente

### Melhorias de Criação
- [x] Criar presente via URL (buscar dados automaticamente)
- [x] Importar produto do Mercado Livre
- [x] Importar produto da Amazon
- [x] Busca de produtos integrada
- [ ] Adicionar múltiplas imagens
- [ ] Galeria de imagens do presente
- [ ] Vídeo do produto (YouTube, Vimeo)
- [x] QR Code do link do produto
- [ ] Preço mínimo e máximo
- [ ] Prioridade do presente (alta, média, baixa)
- [ ] Código SKU do produto

### Melhorias de Apresentação
- [ ] Visualização em grid
- [ ] Visualização em lista
- [ ] Ordenar por preço (menor/maior)
- [ ] Ordenar por nome
- [ ] Ordenar por avaliação
- [ ] Filtrar por categoria
- [ ] Filtrar por status (comprado/disponível)
- [ ] Filtrar por preço range
- [ ] Mostrar apenas presentes disponíveis

### Sistema de Reserva/Compra
- [ ] Reservar presente (evitar duplicidade)
- [ ] Cancelar reserva
- [ ] Tempo de reserva (expira em X dias)
- [ ] Ver quem reservou (anonimato opcional)
- [ ] Notificar dono sobre reserva
- [ ] Marcar como comprado efetivamente
- [ ] Confirmar recebimento do presente
- [ ] Valor gasto total no presente

### Sistema de Avaliação
- [ ] Comentários no presente
- [ ] Fotos do presente adquirido
- [ ] Avaliação por estrelas
- [ ] Avaliação por texto
- [ ] Ver avaliações de outros
- [ ] média de avaliações

### Funcionalidades Extras
- [ ] Wishlist/wishlist (salvar para depois)
- [ ] Compartilhar presente específico
- [ ] Comparar preços de diferentes lojas
- [ ] Alerta de mudança de preço
- [ ] Ver histórico de preço
- [ ] Adicionar à lista de outro usuário
- [ ] Cupons de desconto integrados

---

## Categorias

### Funcionalidades (Já Implementado)
- [x] Criar categoria com nome
- [x] Listar categorias do usuário
- [x] Editar categoria

### Funcionalidades Faltantes
- [x] Excluir categoria (não existe método destroy!)
- [ ] Reordenar categorias (ordenar manualmente)
- [ ] Categorias padrão do sistema (eletrônicos, vestuário, etc.)
- [ ] Importar categorias de outros usuários
- [ ] Compartilhar categorias como modelo
- [ ] Criar categoria a partir de preset
- [ ] Ícone para categoria (emoji ou imagem)
- [ ] Cor da categoria
- [ ] Subcategorias (hierarquia)
- [ ] Merge de categorias (unir duas em uma)
- [ ] Contagem de presentes por categoria
- [ ] Filtrar presentes por categoria na lista

---

## Grupos

### Funcionalidades (Já Implementado)
- [x] Criar grupo com nome e descrição
- [x] Adicionar membros ao criar (via amizades)
- [x] Upload de imagem do grupo
- [x] Listar grupos do usuário
- [x] Ver detalhes do grupo (integrantes, listas)
- [x] Editar grupo
- [x] Atualizar membros

### Funcionalidades Faltantes
- [ ] Excluir grupo (não existe método destroy!)
- [ ] Sair do grupo
- [ ] Remover membro do grupo
- [ ] Permissões por membro (admin, editor, visualizador)
- [ ] Tornar outro usuário administrador
- [ ] Solicitar entrada no grupo
- [ ] Aceitar/rejeitar solicitação de entrada
- [ ] Convite por link compartilhável
- [ ] Convite por username/email
- [ ] Notificar membros de novas listas
- [ ] Chat do grupo
- [ ] Histórico de atividades do grupo
- [ ] Ver quem visualizou o grupo
- [ ] Estatísticas do grupo (listas, presentes)
- [ ] Grupo público (visível para não membros)
- [ ] Grupo privado (precisa aprovação)
- [ ] Configurações de privacidade
- [ ] Definir regras do grupo
- [ ] Descrição detalhada do grupo
- [ ] Banner do grupo

---

## Sistema de Amizades

### Funcionalidades (Já Implementado)
- [x] Buscar usuários por nome/username
- [x] Enviar solicitação de amizade
- [x] Aceitar solicitação de amizade
- [x] Listar amizades ativas
- [x] Listar solicitações pendentes
- [x] Ver perfil do amigo (listas, grupos)
- [x] Ver grupos em comum
- [x] Remover amizade
- [x] Buscar usuários via API

### Funcionalidades Faltantes
- [ ] Rejeitar solicitação de amizade
- [ ] Cancelar solicitação enviada
- [ ] Contador de amizades no perfil
- [ ] Sugestões de amigos (baseado em amigos em comum)
- [ ] Encontrar amigos via lista de contatos
- [ ] Importar contatos do Gmail/Outlook
- [ ] Notificação de nova solicitação
- [ ] Notificação de amizade aceita
- [ ] Histórico de amizades (amigos antigos)
- [ ] Amigos bloqueados
- [ ] Desbloquear usuário
- [ ] Denunciar usuário
- [ ] Ver quando o amigo foi visto online
- [ ] Última vez que o amigo acessou
- [ ] Configurações de privacidade das amizades
- [ ] Tornar amizade pública/privada
- [ ] Lista de amigos pública ou privada
- [ ] Estatísticas (amigos adicionados este mês)
- [ ] Ordenar amigos (alfabético, recente)
- [ ] Filtrar amigos por grupo

---

## Dashboard

### Funcionalidades (Já Implementado)
- [x] Exibir listas ativas do usuário (5 mais recentes)
- [x] Exibir grupos recentes (5 mais recentes)
- [x] Exibir solicitações de amizade pendentes

### Funcionalidades Faltantes
- [ ] Estatísticas gerais (total de listas, presentes, amigos, grupos)
- [ ] Resumo de gastos (presentes dados este mês/ano)
- [ ] Lista de presentes comprados/reservados recentemente
- [ ] Próximos eventos (listas com data próxima)
- [ ] Contador regressivo para próximos eventos
- [ ] Listas criadas vs listas que participo
- [ ] Atividade recente (quem reservou/comprou presentes)
- [ ] Notificações não lidas
- [ ] Atalhos rápidos (criar lista, adicionar amigo, etc.)
- [ ] Boas-vindas personalizadas (nome, horário do dia)
- [ ] Tutoriais para novos usuários
- [ ] Dicas e sugestões
- [ ] Aniversariantes do mês (amigos)
- [ ] Listas públicas recomendadas
- [ ] Widget de busca
- [ ] Theme toggle (claro/escuro)
- [ ] Layout personalizável (arrastar widgets)
- [ ] Exportar dados do dashboard
- [ ] Modo apresentação (tela cheia)

---

## Notas

- Funcionalidades organizadas por prioridade baseada em valor para o usuário
- Cada seção representa uma análise独立的 das funcionalidades existentes
- [x] = já implementado, [ ] = pendente