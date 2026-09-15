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

COPY src/notify-contract/moon.mod src/notify-contract/moon.pkg /workspace/notify-contract/
COPY src/notify-contract/*.mbt /workspace/notify-contract/
COPY src/notify-publish/moon.mod src/notify-publish/moon.pkg src/notify-publish/moon.work /workspace/notify-publish/
COPY src/notify-publish/*.mbt /workspace/notify-publish/
COPY src/notify-publish/cmd /workspace/notify-publish/cmd

WORKDIR /workspace/notify-publish

RUN moon update \
  && moon build --target native --release

FROM node:24.17.0-slim

ENV TZ=Asia/Tokyo
ENV PNPM_HOME=/pnpm
ENV PATH=${PNPM_HOME}:${PATH}

WORKDIR /app

RUN apt-get update \
  && apt-get install -y \
    ca-certificates \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY --from=notify-publish-builder /workspace/notify-publish/_build/native/release/build/isekai-observatory/notify-publish/cmd/main/main.exe \
  /usr/local/bin/notify-publish
COPY infra/production/docker/viewer/viewer-deploy /usr/local/bin/viewer-deploy
COPY mise.toml ./

RUN corepack enable \
  && corepack prepare "pnpm@$(sed -n 's/^pnpm = "\(.*\)"$/\1/p' mise.toml)" --activate \
  && chmod +x /usr/local/bin/viewer-deploy /usr/local/bin/notify-publish

WORKDIR /app/src

COPY src/package.json src/pnpm-lock.yaml src/pnpm-workspace.yaml ./
COPY src/viewer/package.json viewer/package.json

RUN pnpm install --frozen-lockfile --filter viewer --prod \
  && rm -rf /pnpm/store

COPY src/viewer viewer

ENTRYPOINT ["viewer-deploy"]
