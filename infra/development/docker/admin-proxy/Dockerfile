FROM node:24.21.0-slim

ENV TZ=Asia/Tokyo
ENV PNPM_HOME=/pnpm
ENV PATH=${PNPM_HOME}:${PATH}

WORKDIR /app

RUN apt-get update \
  && apt-get install -y \
    ca-certificates \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY infra/development/docker/admin-proxy/admin-proxy-deploy /usr/local/bin/admin-proxy-deploy
COPY mise.toml ./

RUN corepack enable \
  && corepack prepare "pnpm@$(sed -n 's/^pnpm = "\(.*\)"$/\1/p' mise.toml)" --activate \
  && chmod +x /usr/local/bin/admin-proxy-deploy

WORKDIR /app/src

COPY src/app/package.json src/app/pnpm-lock.yaml src/app/pnpm-workspace.yaml ./
COPY src/app/admin-proxy/package.json admin-proxy/package.json

RUN pnpm install --frozen-lockfile --filter admin-proxy --prod \
  && rm -rf /pnpm/store

COPY src/app/admin-proxy admin-proxy

ENTRYPOINT ["admin-proxy-deploy"]
