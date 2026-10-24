// 一覧の並び替えはビルド時に順位を決めて dataset に載せ、ブラウザ側は数値を比べるだけにする

const titleCollator = new Intl.Collator('ja');

// 渡した並びでの位置を順位にする
export const orderRanks = <T>(items: readonly T[]): Map<T, number> => new Map(items.map((item, rank) => [item, rank]));

export const sortRanks = <T>(items: readonly T[], compare: (left: T, right: T) => number): Map<T, number> =>
  orderRanks([...items].sort(compare));

// 読み仮名を持たないので、漢字の並びは文字コード順に近くなる
export const byTitle = (left: { title: string }, right: { title: string }): number =>
  titleCollator.compare(left.title, right.title);
