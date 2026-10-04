/**
 * Web フォント定義
 *
 * 外部 CDN に依存しないよう self-host する
 * 読み込むのは Tailwind のユーティリティで使っているウェイトだけに絞る
 * IBM Plex Sans JP は unicode-range で分割された CSS なので、必要な字形の分だけ転送される
 */

// IBM Plex Sans JP: 本文 400 / font-medium 500 / font-semibold 600 / font-bold 700
import '@fontsource/ibm-plex-sans-jp/400.css';
import '@fontsource/ibm-plex-sans-jp/500.css';
import '@fontsource/ibm-plex-sans-jp/600.css';
import '@fontsource/ibm-plex-sans-jp/700.css';

// IBM Plex Mono: ID や日付などの等幅表示 400 / 500
import '@fontsource/ibm-plex-mono/400.css';
import '@fontsource/ibm-plex-mono/500.css';
