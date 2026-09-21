FROM debian:bookworm-slim AS builder

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

COPY src/notification/contract/moon.mod src/notification/contract/moon.pkg /workspace/notify-contract/
COPY src/notification/contract/*.mbt /workspace/notify-contract/
COPY src/notification/discord-notifier/moon.mod src/notification/discord-notifier/moon.pkg src/notification/discord-notifier/moon.work /workspace/discord-notifier/
COPY src/notification/discord-notifier/*.mbt /workspace/discord-notifier/
COPY src/notification/discord-notifier/cmd /workspace/discord-notifier/cmd

WORKDIR /workspace/discord-notifier

RUN moon update \
  && moon build --target native --release

FROM debian:bookworm-slim

ENV TZ=Asia/Tokyo \
  PORT=8080

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    ca-certificates \
    tzdata \
  && rm -rf /var/lib/apt/lists/* \
  && groupadd --system app \
  && useradd --system --gid app --home-dir /home/app --create-home \
    --shell /usr/sbin/nologin app

COPY --from=builder /workspace/discord-notifier/_build/native/release/build/isekai-observatory/discord-notifier/cmd/main/main.exe /usr/local/bin/discord-notifier

USER app

EXPOSE 8080

CMD ["discord-notifier"]
