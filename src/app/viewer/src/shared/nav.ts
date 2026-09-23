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
    iconPaths: ['M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z', 'M12 14.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z'],
  },
  // 未提供ページの雛形。公開時にアイコンを決めてコメントを外す
  // { href: '/media', label: 'メディア', iconPaths: [] },
  // { href: '/profile', label: '人物', iconPaths: [] },
];
