// サイト自身を指す表記
// 表示名やデフォルトの OG 画像はここを Source of Truth とする

export const SITE_TITLE = 'ヰ世界観測所';

export const SITE_DESCRIPTION
  = 'ヰ世界情緒の楽曲・リリース情報を記録する非公式ファンサイトです';

export type OgImage = {
  url: string;
  alt: string;
};

/**
 * ページ固有の OG 画像がないときに使うサイトアイコン
 * public/og-default.png は logo-dark.svg を 1200x630 の背景 (#0e0e10) の中央へ幅 560 で置いて書き出したもの
 * 正方形に切り取られるカードでも欠けないよう、マークは中央 630px の内側に収めている
 */
export const DEFAULT_OG_IMAGE: OgImage = {
  url: '/og-default.png',
  alt: `${SITE_TITLE}のロゴ`,
};
