# CHECKLIST PARA RESOLVER PROBLEMA NO SERVIDOR

## 1. Verificações Básicas no Servidor

### a) Upload dos arquivos
```bash
# Verificar se todos os arquivos foram enviados corretamente
ls -la app/Http/Controllers/ChatController.php
ls -la app/Services/LocationService.php  
ls -la resources/views/welcome.blade.php
```

### b) Permissões
```bash
# Verificar permissões dos diretórios
chmod -R 755 app/
chmod -R 755 resources/
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/
```

### c) Limpar caches no servidor
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 2. Verificar Logs do Servidor

### a) Logs do Laravel
```bash
tail -f storage/logs/laravel.log
```

### b) Logs do servidor web (Apache/Nginx)
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx  
tail -f /var/log/nginx/error.log
```

## 3. Testar Funcionalidade Específica

### a) Executar script de diagnóstico no servidor
```bash
php diagnose_server.php
```

### b) Executar teste de nicknames no servidor  
```bash
php test_nicknames.php
```

### c) Testar sintaxe PHP no servidor
```bash
php -l app/Http/Controllers/ChatController.php
```

## 4. Comparar Versões

### a) Versão do PHP
- Local: 8.3.23
- Servidor: ? (verificar com `php -v`)

### b) Versão do Laravel
- Local: 10.48.29
- Servidor: ? (verificar com `php artisan --version`)

## 5. Verificar Diferenças de Configuração

### a) Arquivo .env
- Verificar se APP_DEBUG=true no servidor
- Verificar configuração do banco de dados
- Verificar se não há caracteres especiais/quebras de linha

### b) Configuração do servidor web
- Verificar se mod_rewrite está ativo (Apache)
- Verificar configuração do DocumentRoot
- Verificar se .htaccess está sendo lido

## 6. Problemas Comuns

### a) Cache do navegador
- Forçar refresh com Ctrl+F5
- Limpar cache do navegador
- Testar em modo privado/incógnito

### b) Cache do CDN/Cloudflare
- Se usar Cloudflare, limpar cache
- Verificar se arquivos JS/CSS estão sendo atualizados

### c) Diferenças de timezone
- Verificar timezone do servidor vs local

## 7. Debug Específico para Chat

### a) Testar endpoint diretamente
```bash
# Testar se o endpoint responde
curl -X POST -H "Content-Type: application/json" \
     -H "X-CSRF-TOKEN: test" \
     -d '{}' \
     https://seudominio.com/chat/find-room
```

### b) Verificar se JavaScript está carregando
- Abrir Developer Tools (F12)
- Verificar se há erros no Console
- Verificar se arquivos JS estão sendo carregados na aba Network

## 8. Comando de Emergência

Se nada funcionar, reverter para estado anterior:
```bash
git checkout HEAD~1  # Voltar 1 commit
# ou
git reset --hard HEAD~1  # Reset completo (cuidado!)
```

## IMPORTANTE
Antes de qualquer alteração no servidor de produção:
1. Fazer backup do banco de dados
2. Fazer backup dos arquivos
3. Testar em ambiente de staging primeiro
