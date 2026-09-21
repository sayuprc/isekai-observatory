export function kindLabel(kind: string): string {
  return (
    {
      song: '楽曲',
      release: 'リリース',
      media: 'メディア',
      event: '出来事',
    }[kind] ?? kind
  );
}
