// Home はブランドロゴから遷移できるためナビには含めない
// ナビの知識 (リンク先・ラベル・アイコン) はここが Source of Truth で、Header / BottomNav / Footer はここから導出する

export type NavLink = {
  href: string;
  label: string;
  // BottomNav で描画する 24x24 stroke アイコンのパス
  iconPaths: readonly string[];
};

export const navLinks: readonly NavLink[] = [
  {
    href: '/songs',
    label: '楽曲',
    iconPaths: ['M9 18V5l12-2v13', 'M6 21a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z', 'M18 19a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
  },
  {
    href: '/releases',
    label: 'リリース',
    iconPaths: [
      'M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z',
      'M12 17a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z',
      'M12 12h.01',
    ],
  },
  {
    href: '/events',
    label: 'イベント',
    iconPaths: [
      'M8 2v4',
      'M16 2v4',
      'M3 10h18',
      'M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z',
    ],
  },
  {
    href: '/media',
    label: 'メディア',
    iconPaths: ['M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z', 'm10 9 5 3-5 3Z'],
  },
  // 未提供ページの雛形。公開時にアイコンを決めてコメントを外す
  // { href: '/profile', label: '人物', iconPaths: [] },
];
