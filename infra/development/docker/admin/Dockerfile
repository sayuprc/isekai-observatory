FROM oven/bun:1.4.2 AS build

ENV MISE_AUTO_INSTALL=false

WORKDIR /app

RUN apt-get update \
  && apt-get install -y \
    ca-certificates \
    curl \
    libatomic1 \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && curl https://mise.run | sh

ENV PATH=/root/.local/bin:${PATH}
# 環境バッジの表示判定に使う
ENV APP_ENV=development

COPY mise.toml ./

RUN mise trust \
  && mise install pnpm

WORKDIR /app/src

COPY src/package.json src/pnpm-lock.yaml src/pnpm-workspace.yaml ./
COPY src/admin/package.json admin/package.json

RUN mise exec -- pnpm install --frozen-lockfile --filter admin --prod

COPY src/admin admin

ARG PUBLIC_APP_URL
ENV PUBLIC_APP_URL=${PUBLIC_APP_URL}
ENV ADMIN_SSR_NO_EXTERNAL=true

RUN bun --filter admin build

FROM oven/bun:1.4.2-slim AS runtime

ENV TZ=Asia/Tokyo

WORKDIR /app

COPY --from=build /app/src/admin/dist /app/dist

EXPOSE 8080

CMD ["bun", "/app/dist/server/entry.mjs"]
