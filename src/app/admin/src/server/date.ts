const DATETIME_LOCAL_PATTERN = /^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})(?::(\d{2}))?$/;

export const normalizeDateTimeApiValue = (value: string): string => {
  const normalized = value.trim();
  const match = normalized.match(DATETIME_LOCAL_PATTERN);

  if (!match) {
    return normalized;
  }

  const [, date, hourMinute, second = '00'] = match;

  return `${date}T${hourMinute}:${second}+09:00`;
};
