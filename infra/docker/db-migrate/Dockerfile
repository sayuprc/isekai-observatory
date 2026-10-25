FROM jdxcode/mise:2026.10.18-debian AS atlas

ENV MISE_AUTO_INSTALL=false

WORKDIR /workspace

COPY mise.toml ./

RUN mise trust \
  && mise install atlas \
  && install -Dm755 "$(mise which atlas)" /out/atlas

FROM alpine:3.24

ENV TZ=Asia/Tokyo
# 環境名は各環境の ci.yaml から --build-arg で渡す
ARG DEPLOY_ENV
ENV DEPLOY_ENV=${DEPLOY_ENV}

WORKDIR /app/src/app/server/database

RUN apk add --no-cache \
    ca-certificates \
    tzdata \
  && addgroup -S app \
  && adduser -S -G app app

COPY --from=atlas /out/atlas /usr/local/bin/atlas
COPY infra/docker/db-migrate/db-migrate /usr/local/bin/db-migrate
COPY src/app/server/database ./

RUN test -n "${DEPLOY_ENV}" \
  && chmod +x /usr/local/bin/db-migrate \
  && chown -R app:app /app

USER app

ENTRYPOINT ["db-migrate"]
