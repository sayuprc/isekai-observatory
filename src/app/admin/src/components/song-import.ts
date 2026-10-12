export type CandidateSong = {
  songId: string;
  title: string;
};

/**
 * Event のセットリスト項目や Release の収録曲から、もう片方へ取り込む候補
 * songs は項目内の順に並べ、2 件以上ならメドレー、空なら楽曲を参照しない行 (表示名だけのセットリスト項目、タイトルのみトラック)
 * label はセットリスト項目の表示名やトラック名で、楽曲を参照しない行ではタイトルになる
 */
export type SongCandidate = {
  key: string;
  songs: CandidateSong[];
  label: string;
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

// 候補の一覧に出す名前。楽曲を参照しない行は表示名、楽曲を参照する行は楽曲名を並べる
export const candidateTitle = (candidate: SongCandidate): string =>
  candidate.songs.length === 0 ? candidate.label : candidate.songs.map((song) => song.title).join(' / ');
