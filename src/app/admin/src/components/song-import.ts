/**
 * Event のセットリストや Release の収録楽曲から、もう片方へ取り込む楽曲の候補
 * songId が null の候補は楽曲を参照しない行 (表示名だけのセットリスト項目、タイトルのみトラック)
 */
export type SongCandidate = {
  key: string;
  songId: string | null;
  title: string;
  // 取り込み元での位置や表示名など、候補を見分けるための補足
  note: string;
  selectable: boolean;
  defaultSelected: boolean;
};

export const defaultSelectedKeys = (candidates: SongCandidate[]): Set<string> =>
  new Set(candidates.filter((candidate) => candidate.selectable && candidate.defaultSelected).map(({ key }) => key));

// 取り込み元の並び順を保ったまま、選択中の候補だけを返す
export const pickSelectedCandidates = (
  candidates: SongCandidate[],
  selectedKeys: ReadonlySet<string>,
): SongCandidate[] => candidates.filter((candidate) => candidate.selectable && selectedKeys.has(candidate.key));
