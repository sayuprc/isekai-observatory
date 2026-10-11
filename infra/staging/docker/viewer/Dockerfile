FROM debian:bookworm-slim AS notify-publish-builder

# moonc の version 文字列は mise.toml の moonbit_version から --build-arg で渡す
ARG MOONBIT_VERSION

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    bash \
    build-essential \
    ca-certificates \
    curl \
    git \
  && rm -rf /var/lib/apt/lists/*

ENV PATH="/root/.moon/bin:${PATH}"

RUN test -n "${MOONBIT_VERSION}" \
  && curl -fsSL https://cli.moonbitlang.com/install/unix.sh \
  | bash -s -- "${MOONBIT_VERSION}"

WORKDIR /workspace

COPY src/notification/contract/moon.mod src/notification/contract/moon.pkg /workspace/contract/
COPY src/notification/contract/*.mbt /workspace/contract/
COPY src/notification/publish/moon.mod src/notification/publish/moon.pkg src/notification/publish/moon.work /workspace/publish/
COPY src/notification/publish/*.mbt /workspace/publish/
COPY src/notification/publish/cmd /workspace/publish/cmd

WORKDIR /workspace/publish

RUN moon update \
  && moon build --target native --release

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

COPY --from=notify-publish-builder /workspace/publish/_build/native/release/build/isekai-observatory/notify-publish/cmd/main/main.exe \
  /usr/local/bin/notify-publish
COPY infra/staging/docker/viewer/viewer-deploy /usr/local/bin/viewer-deploy
COPY mise.toml ./

RUN corepack enable \
  && corepack prepare "pnpm@$(sed -n 's/^pnpm = "\(.*\)"$/\1/p' mise.toml)" --activate \
  && chmod +x /usr/local/bin/viewer-deploy /usr/local/bin/notify-publish

WORKDIR /app/src

COPY src/app/package.json src/app/pnpm-lock.yaml src/app/pnpm-workspace.yaml ./
COPY src/app/viewer/package.json viewer/package.json

RUN pnpm install --frozen-lockfile --filter viewer --prod \
  && rm -rf /pnpm/store

COPY src/app/viewer viewer

ENTRYPOINT ["viewer-deploy"]
