/**
 * Web フォント定義
 *
 * self-host して HTML → CSS → Google Fonts の CSS → woff2 という直列の取得を 1 段短くする
 * 読み込むのは viewer.css で実際に指定しているバリアントだけに絞る
 * 日本語 2 ファミリーは unicode-range で分割された CSS を選び、必要な字形の分だけ転送させる
 */

// Noto Serif JP: 本文 400 / サイトポリシーの見出し 600 / 見出し 700
import '@fontsource/noto-serif-jp/400.css';
import '@fontsource/noto-serif-jp/600.css';
import '@fontsource/noto-serif-jp/700.css';

// Shippori Mincho: 小見出し 400 / セクション見出し 500 / ブランド表記 600
import '@fontsource/shippori-mincho/400.css';
import '@fontsource/shippori-mincho/500.css';
import '@fontsource/shippori-mincho/600.css';

// EB Garamond: 欧文の添え書きは italic のみで、normal 400 は .font-en 用
import '@fontsource/eb-garamond/400.css';
import '@fontsource/eb-garamond/400-italic.css';
import '@fontsource/eb-garamond/500-italic.css';

// JetBrains Mono: ラベル 400 / サムネイル上のオーバーレイ 500
import '@fontsource/jetbrains-mono/400.css';
import '@fontsource/jetbrains-mono/500.css';

// Spectral: 数値表記に使う normal 500 と italic 400 のみ
import '@fontsource/spectral/500.css';
import '@fontsource/spectral/400-italic.css';
