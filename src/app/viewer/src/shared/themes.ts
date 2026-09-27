// カラーテーマ定義
// 衣装をベースにした配色のメタデータ
// 実際の色値は src/styles/tokens.css の [data-palette="..."] が Source of Truth で、ここは value / 名前 / swatch を保持する

export type ThemeTone = 'dark' | 'light';

export type Theme = {
  value: string;
  name: string;
  tone: ThemeTone;
  swatch: [string, string, string, string];
};

export const THEMES: Theme[] = [
  {
    value: 'anemone-1',
    name: 'Anemone I',
    tone: 'dark',
    swatch: ['#0E0E10', '#D31B28', '#FFFFFF', '#1C1C21'],
  },
  {
    value: 'anemone-2',
    name: 'Anemone II',
    tone: 'light',
    swatch: ['#F3EEEE', '#60C3D0', '#4D4E88', '#E2D9DC'],
  },
  {
    value: 'nemophila-1',
    name: 'Nemophila I',
    tone: 'light',
    swatch: ['#F5F7FA', '#1C8BF4', '#22252A', '#D2E6FF'],
  },
  {
    value: 'nemophila-2',
    name: 'Nemophila II',
    tone: 'dark',
    swatch: ['#111318', '#1D70EC', '#D4D9E2', '#1C2029'],
  },
  {
    value: 'sunflower-1',
    name: 'Sunflower',
    tone: 'light',
    swatch: ['#FAF9F6', '#FAD423', '#317781', '#FEF9C3'],
  },
  // {
  //   value: 'sunflower-2',
  //   name: 'Sunflower II',
  //   tone: 'dark',
  //   swatch: ['#0F0F11', '#C5A358', '#DC2626', '#222227'],
  // },
];

export const LIGHT_PALETTES: Set<string> = new Set(
  THEMES.filter((theme) => theme.tone === 'light').map((theme) => theme.value),
);

export const DEFAULT_PALETTE = 'anemone-1';

export const PALETTE_STORAGE_KEY = 'isekai-observatory-palette';

// 未知の値 (廃止テーマ等) は既定テーマに落とす
export const themeByValue = (value: string): Theme =>
  THEMES.find((theme) => theme.value === value) ?? THEMES.find((theme) => theme.value === DEFAULT_PALETTE) ?? THEMES[0];
