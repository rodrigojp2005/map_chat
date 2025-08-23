# Sistema de Chat MapChat 💬

## Visão Geral

O MapChat implementa um sistema de chat em tempo real baseado em proximidade geográfica. Os usuários são automaticamente agrupados em salas de chat baseadas na sua localização, criando uma experiência de conversa local dinâmica.

## Características Principais

### 🎯 Salas Inteligentes
- **Criação automática**: Salas são criadas automaticamente baseadas na concentração geográfica de usuários
- **Raio adaptativo**: O raio das salas ajusta-se dinamicamente (50km - 500km) baseado na densidade de usuários
- **Reorganização inteligente**: Salas são divididas quando lotadas (>100 usuários) ou mescladas quando com poucos usuários (<5)

### 👥 Suporte Multi-Usuário
- **Usuários registrados**: Perfil completo com nome e avatar persistente
- **Usuários anônimos**: Sistema de nickname temporário para sessão
- **Limite flexível**: Até 100 usuários por sala (configurável)

### 🔄 Sistema em Tempo Real
- **Polling inteligente**: Mensagens atualizadas a cada 3 segundos
- **Heartbeat**: Manter usuários ativos com ping a cada 30 segundos  
- **Notificações**: Alertas visuais para novas mensagens quando chat fechado

### 🧹 Auto-Limpeza
- **Usuários inativos**: Remoção após 5 minutos de inatividade
- **Salas vazias**: Desativação automática quando todos saem
- **Dados antigos**: Limpeza automática de mensagens antigas

## Arquitetura do Sistema

### Backend (Laravel)

```
📁 Models/
├── ChatRoom.php          # Gerenciamento de salas
├── ChatMessage.php       # Mensagens do chat  
├── ChatRoomUser.php      # Usuários nas salas
└── AnonymousUser.php     # Usuários não registrados

📁 Services/
├── ChatRoomService.php   # Lógica principal do chat
└── LocationService.php   # Integração com geolocalização

📁 Controllers/
└── ChatController.php    # API REST para o chat

📁 Commands/
└── ChatCleanupCommand.php # Manutenção automatizada
```

### Frontend (JavaScript)

```
📁 resources/js/
└── chat-manager.js       # Interface completa do chat
```

### Base de Dados

```sql
-- Salas de chat com informações geográficas
chat_rooms (id, room_id, name, center_latitude, center_longitude, radius_km, max_users, current_users, is_active, last_activity)

-- Mensagens das salas
chat_messages (id, chat_room_id, user_id, user_name, user_type, avatar_type, message, message_type, sent_at, is_visible)

-- Usuários ativos nas salas
chat_room_users (id, chat_room_id, user_id, user_name, user_type, avatar_type, latitude, longitude, joined_at, last_seen, is_active)
```

## API Endpoints

### Públicos (sem autenticação)
```bash
POST /chat/find-room         # Encontrar/criar sala baseada na localização
GET  /chat/{room}/messages   # Carregar mensagens da sala
POST /chat/{room}/send       # Enviar mensagem
GET  /chat/{room}/users      # Usuários online na sala
POST /chat/{room}/leave      # Sair da sala
GET  /chat/{room}/info       # Informações da sala
POST /chat/{room}/heartbeat  # Manter usuário ativo
POST /chat/set-nickname      # Definir nickname (anônimos)
```

## Algoritmo de Clustering Geográfico

### 1. Detecção de Proximidade
```javascript
// Buscar salas existentes dentro de 500km
const nearbyRoom = findRoomsWithinRadius(userLat, userLng, 500);
```

### 2. Cálculo de Raio Ótimo
```javascript
// Ajustar raio baseado na densidade local
if (nearbyUsers <= 2)  radius = 500km;  // Poucos usuários = raio maior
if (nearbyUsers <= 10) radius = 200km;  // Densidade média
if (nearbyUsers <= 50) radius = 100km;  // Densidade padrão  
if (nearbyUsers > 50)  radius = 50km;   // Muitos usuários = raio menor
```

### 3. Reorganização Dinâmica
```javascript
// Dividir salas lotadas
if (roomUsers > maxUsers) {
  splitRoomByGeographicCenter(room);
}

// Mesclar salas vazias
if (roomUsers < 5) {
  tryMergeWithNearbyRoom(room);
}
```

## Fluxo do Usuário

### Para Usuários Anônimos
1. **Configuração inicial**: Avatar + localização no LocationManager
2. **Nickname**: Escolher nickname único para a sessão
3. **Sala automática**: Sistema encontra/cria sala baseada na proximidade
4. **Chat ativo**: Envio/recebimento de mensagens em tempo real
5. **Limpeza**: Desconexão automática ao sair/tempo limite

### Para Usuários Registrados
1. **Login**: Autenticação padrão do Laravel
2. **Configuração**: Avatar + localização (salva no perfil)
3. **Sala automática**: Sistema encontra/cria sala baseada na proximidade  
4. **Chat persistente**: Mensagens e participação salvos no histórico
5. **Perfil**: Avatar e localização mantidos entre sessões

## Interface do Chat

### Estados Visuais
- **🟡 Conectando**: Buscando sala baseada na localização
- **🟢 Conectado**: Chat ativo e funcional
- **🔴 Erro**: Problema de conexão ou configuração
- **💬 Notificação**: Contador de mensagens não lidas

### Componentes UI
- **Botão Toggle**: Abrir/fechar chat (bottom-right)
- **Painel Principal**: Mensagens + input + usuários online
- **Modal Nickname**: Configuração inicial para anônimos
- **Notificações**: Badge com contador de mensagens

## Comandos de Manutenção

### Limpeza Manual
```bash
php artisan chat:cleanup  # Executa limpeza completa
```

### Limpeza Automática (Kernel)
```php
$schedule->command('chat:cleanup')->everyFifteenMinutes();
```

### Estatísticas
```bash
# Salas ativas
SELECT COUNT(*) FROM chat_rooms WHERE is_active = 1;

# Mensagens hoje  
SELECT COUNT(*) FROM chat_messages WHERE sent_at > CURRENT_DATE;

# Usuários ativos agora
SELECT COUNT(*) FROM chat_room_users WHERE is_active = 1 AND last_seen > NOW() - INTERVAL 5 MINUTE;
```

## Configuração e Deploy

### Dependências NPM
```bash
npm install laravel-echo socket.io-client pusher-js
```

### Migrações
```bash
php artisan migrate  # Cria tabelas chat_rooms, chat_messages, chat_room_users
```

### Configuração de Ambiente
```bash
# .env
BROADCAST_DRIVER=log  # ou pusher para WebSocket real
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key  
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=mt1
```

## Monitoramento e Performance

### Métricas Importantes
- **Latência de mensagens**: Tempo entre envio e recebimento
- **Número de salas ativas**: Indicador de engajamento
- **Taxa de reorganização**: Frequência de split/merge de salas
- **Usuários simultâneos por sala**: Distribuição e balanceamento

### Otimizações Implementadas
- **Índices de database**: Otimização de queries geográficas
- **Polling inteligente**: Apenas quando necessário
- **Limpeza automática**: Previne acúmulo de dados
- **Cache de posições**: Reduz cálculos de distância

## Exemplo de Uso

```javascript
// Inicialização automática
const chatManager = new ChatManager(locationManager);

// O chat será habilitado automaticamente quando:
// 1. LocationManager for configurado (avatar + localização)
// 2. Nickname for definido (para anônimos)
// 3. Sala for encontrada/criada baseada na proximidade

// Interface aparece no canto inferior direito
// Usuários podem conversar instantaneamente com pessoas próximas
```

## Roadmap Futuro

### Funcionalidades Planejadas
- **WebSocket real**: Migrar de polling para WebSocket (Pusher/Socket.io)
- **Moderação**: Sistema de report e moderação de mensagens
- **Emojis**: Suporte a reações e emojis customizados
- **Histórico**: Salvar histórico de mensagens para usuários registrados
- **Notificações Push**: Alertas mesmo quando app fechado

### Melhorias Técnicas
- **Redis**: Cache para posições e salas ativas
- **Queue**: Processamento assíncrono de reorganização de salas
- **Analytics**: Dashboard com métricas de uso do chat
- **Rate Limiting**: Prevenção de spam e abuso

---

💡 **Nota**: O sistema foi projetado para funcionar tanto para usuários anônimos (temporários) quanto registrados (persistentes), com foco na experiência local e descoberta de pessoas próximas geograficamente.
