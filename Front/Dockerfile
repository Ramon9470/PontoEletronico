# Front/Dockerfile
FROM node:18-alpine

# Argumentos de permissão
ARG user=node
ARG uid=1000

WORKDIR /app

# Instala o Angular CLI globalmente
RUN npm install -g @angular/cli@16

# Instala dependências do sistema necessárias 
RUN apk add --no-cache git

# Expõe a porta do Angular
EXPOSE 4200

# O comando de inicialização
CMD ["ng", "serve", "--host", "0.0.0.0", "--disable-host-check"]