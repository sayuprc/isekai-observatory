// 表示用の日付はドット区切り (YYYY.MM.DD) に揃える

// YYYY-MM-DD や MM-DD をドット区切りにする
export const dottedDate = (value: string): string => value.replaceAll('-', '.');

// 日時は日本時間の日付にしてドット区切りにする
export const dottedDateOfDateTime = (value: string): string =>
  dottedDate(new Intl.DateTimeFormat('sv-SE', { timeZone: 'Asia/Tokyo' }).format(new Date(value)));
