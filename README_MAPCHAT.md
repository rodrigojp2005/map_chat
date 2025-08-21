# MapChat - Sistema de Localização Anônima

## 📍 Visão Geral

O MapChat agora é uma plataforma onde os usuários aparecem no mapa com localização anônima para preservar a privacidade. Quando um usuário acessa a plataforma, sua localização real é obtida e uma posição aleatória é gerada dentro de um raio configurável, mantendo o anonimato.

## 🔧 Principais Funcionalidades

### 1. **Localização Anônima Inteligente**
- Obtém a localização real do usuário via GPS
- Gera uma posição aleatória dentro de um raio de 500m a 5.000km
- Para raios grandes (>1000km), tenta manter no mesmo país
- Preserva a privacidade enquanto permite interação social

### 2. **Sistema de Avatares**
- 6 tipos de avatar disponíveis:
  - 👤 Padrão (default.gif)
  - 👨 Homem (mario.gif)
  - 👩 Mulher (girl.gif)
  - 🐕 Pet (pets.gif)
  - 🤓 Geek (geek.gif)
  - ⚽ Esporte (sport.gif)

### 3. **Controle de Privacidade**
- Slider para ajustar raio de anonimato (500m - 5.000km)
- Atualização em tempo real da posição aleatória
- Status visual da localização

### 4. **Usuários Online**
- Lista de usuários ativos na plataforma
- Atualização automática a cada 30 segundos
- Indicadores visuais de status online

## 🗂️ Estrutura do Banco de Dados

### Campos Adicionados à Tabela `users`:
```sql
latitude DECIMAL(10,8) NULL           -- Posição aleatória (pública)
longitude DECIMAL(11,8) NULL          -- Posição aleatória (pública)
real_latitude DECIMAL(10,8) NULL      -- Posição real (privada)
real_longitude DECIMAL(11,8) NULL     -- Posição real (privada)
privacy_radius INT DEFAULT 50000      -- Raio em metros
avatar_type VARCHAR(255) DEFAULT 'default'
is_online BOOLEAN DEFAULT false
last_seen TIMESTAMP NULL
```

## 🛠️ APIs Disponíveis

### Endpoints Públicos:
- `GET /usuarios-online.json` - Lista usuários online
- `GET /` - Página principal com mapa

### Endpoints Autenticados:
- `POST /location/update` - Atualiza localização do usuário
- `POST /location/avatar` - Atualiza avatar do usuário
- `POST /location/privacy-radius` - Atualiza raio de privacidade
- `POST /location/offline` - Marca usuário como offline

## 📱 Interface de Usuario

### Painel de Controle:
1. **Seleção de Avatar**: Grid com 6 opções de avatar
2. **Controle de Raio**: Slider de 500m a 5.000km
3. **Status de Localização**: Indicador visual do GPS
4. **Mensagem para Visitantes**: Incentivo ao login

### Lista de Usuários Online:
- Avatar e nome do usuário
- Tempo desde última atividade
- Indicador de status online
- Click para focar no mapa

## 🔄 Funcionamento do Sistema

### 1. **Ao Acessar a Plataforma**:
```javascript
// Solicita localização do usuário
navigator.geolocation.getCurrentPosition(callback)

// Para usuários logados: envia para servidor
POST /location/update {
    latitude: real_lat,
    longitude: real_lng,
    privacy_radius: radius_in_meters
}

// Servidor gera posição aleatória e salva
```

### 2. **Geração de Localização Aleatória**:
```php
// LocationService.php
public function generateRandomLocation($realLat, $realLng, $radiusKm)
{
    // Gera ângulo e distância aleatórios
    $angle = rand(0, 360) * (M_PI / 180);
    $distance = sqrt(rand(0, 10000) / 10000) * $radiusInDegrees;
    
    // Calcula nova posição
    $newLat = $realLat + ($distance * cos($angle));
    $newLng = $realLng + ($distance * sin($angle));
    
    return ['latitude' => $newLat, 'longitude' => $newLng];
}
```

### 3. **Atualização Automática**:
- Middleware atualiza `last_seen` a cada request
- Comando `users:clean-offline` roda a cada 5 minutos
- Frontend atualiza lista de usuários a cada 30 segundos

## 🚀 Instalação e Configuração

### 1. Executar Migrações:
```bash
php artisan migrate
```

### 2. Popular com Dados de Exemplo:
```bash
php artisan db:seed --class=UsersLocationSeeder
```

### 3. Configurar Agendador (Opcional):
```bash
# Adicionar ao crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Compilar Assets:
```bash
npm run build
```

## 🔐 Segurança e Privacidade

- **Localização Real**: Nunca exposta publicamente
- **Posição Aleatória**: Atualizada a cada mudança de raio
- **Limpeza Automática**: Usuários offline são limpos automaticamente
- **Validação**: Todas as entradas são validadas no servidor

## 🌍 Comportamento Geográfico

### Raios Pequenos (< 1km):
- Posição aleatória próxima à real
- Ideal para encontrar pessoas na mesma cidade

### Raios Médios (1km - 1000km):
- Permite encontrar pessoas na região
- Mantém privacidade da localização exata

### Raios Grandes (> 1000km):
- Tenta manter no mesmo país
- Para países pequenos, reduz automaticamente o raio
- Máximo de 5.000km

## 🧪 Testando o Sistema

### 1. Criar Usuários de Teste:
```bash
php artisan db:seed --class=UsersLocationSeeder
# Cria 5 usuários em diferentes cidades brasileiras
```

### 2. Verificar API:
```bash
curl http://localhost:8000/usuarios-online.json
```

### 3. Testar Interface:
- Acesse `http://localhost:8000`
- Permita acesso à localização
- Ajuste o raio de privacidade
- Escolha um avatar
- Faça login para aparecer no mapa

## 🔮 Próximos Passos

1. **Sistema de Chat**: Permitir conversas entre usuários próximos
2. **Notificações Push**: Alertar sobre usuários próximos
3. **Filtros Avançados**: Por idade, interesses, etc.
4. **Gamificação**: Pontos por interações, conquistas
5. **Modo Invisível**: Opção para não aparecer no mapa

---

**Desenvolvido com Laravel 10 + JavaScript Vanilla + Google Maps API**
