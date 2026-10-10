/**
 * pageToken は先頭からの件数を base64url にしたもの
 * 契約上は不透明なので、配信方法を変えるときは形式ごと変えてよい
 */
export const encodePageToken = (offset: number): string =>
  btoa(String(offset)).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

/**
 * 読めない token は null を返す
 */
export const decodePageToken = (token: string): number | null => {
  try {
    const decoded = atob(token.replace(/-/g, '+').replace(/_/g, '/'));

    return /^\d+$/.test(decoded) ? Number(decoded) : null;
  } catch {
    return null;
  }
};
