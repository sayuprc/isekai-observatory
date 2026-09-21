const formatter = Intl.DateTimeFormat('ja-JP', {
  year: 'numeric',
  month: '2-digit',
  day: '2-digit',
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
  hour12: false,
});

const normalizeDateTimeInputValue = (value: unknown): string => {
  const parsed = value instanceof Date ? value : typeof value === 'string' ? new Date(value) : null;

  if (!parsed || Number.isNaN(parsed.getTime())) {
    return '';
  }

  const offsetMs = parsed.getTimezoneOffset() * 60 * 1000;

  return new Date(parsed.getTime() - offsetMs).toISOString().slice(0, 19);
};

const normalizeDateTimeDisplayValue = (value: unknown): string => {
  const parsed = value instanceof Date ? value : typeof value === 'string' ? new Date(value) : null;

  return !parsed || Number.isNaN(parsed.getTime()) ? '' : formatter.format(parsed);
};

export { formatter, normalizeDateTimeDisplayValue, normalizeDateTimeInputValue };
