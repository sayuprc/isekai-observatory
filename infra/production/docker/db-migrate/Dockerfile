FROM jdxcode/mise:2026.9.12 AS atlas

ENV MISE_AUTO_INSTALL=false

WORKDIR /workspace

COPY mise.toml ./

RUN mise trust \
  && mise install atlas \
  && install -Dm755 "$(mise which atlas)" /out/atlas

FROM alpine:3.24

ENV TZ=Asia/Tokyo

WORKDIR /app/src/server/database/atlas

RUN apk add --no-cache \
    ca-certificates \
    tzdata \
  && addgroup -S app \
  && adduser -S -G app app

COPY --from=atlas /out/atlas /usr/local/bin/atlas
COPY infra/production/docker/db-migrate/db-migrate /usr/local/bin/db-migrate
COPY src/server/database/atlas ./

RUN chmod +x /usr/local/bin/db-migrate \
  && chown -R app:app /app

USER app

ENTRYPOINT ["db-migrate"]
