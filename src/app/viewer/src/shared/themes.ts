// カラーテーマ定義
// 衣装をベースにした配色のメタデータ
// 実際の色値は src/styles/tokens.css の [data-palette="..."] が Source of Truth で、ここは value / 名前 / swatch を保持する

export type ThemeTone = 'dark' | 'light';

export type ThemeGroupValue = 'universal' | 'variant' | 'parallel' | 'thought';

export type ThemeGroup = {
  value: ThemeGroupValue;
  name: string;
};

export type Theme = {
  value: string;
  name: string;
  group: ThemeGroupValue;
  tone: ThemeTone;
  swatch: [string, string, string, string];
};

// 衣装の分類 (普遍体 / 変異体 / 平行体 / 思念体) で、表示順はこの配列の順
export const THEME_GROUPS: ThemeGroup[] = [
  { value: 'universal', name: '普遍体' },
  { value: 'variant', name: '変異体' },
  { value: 'parallel', name: '平行体' },
  { value: 'thought', name: '思念体' },
];

export const THEMES: Theme[] = [
  {
    value: 'anemone-1',
    name: 'Anemone I',
    group: 'universal',
    tone: 'dark',
    swatch: ['#0E0E10', '#D31B28', '#FFFFFF', '#1C1C21'],
  },
  {
    value: 'anemone-2',
    name: 'Anemone II',
    group: 'universal',
    tone: 'light',
    swatch: ['#F3F0EE', '#66B4D8', '#4D4E88', '#E2DDDF'],
  },
  {
    value: 'nemophila-1',
    name: 'Nemophila I',
    group: 'universal',
    tone: 'light',
    swatch: ['#F5F7FA', '#2373E6', '#22252A', '#D2E6FF'],
  },
  {
    value: 'nemophila-2',
    name: 'Nemophila II',
    group: 'universal',
    tone: 'dark',
    swatch: ['#111318', '#1D70EC', '#D4D9E2', '#1C2029'],
  },
  {
    value: 'sunflower-1',
    name: 'Sunflower I',
    group: 'universal',
    tone: 'light',
    swatch: ['#FAF9F6', '#FAD423', '#36718F', '#FEF9C3'],
  },
  {
    value: 'sunflower-2',
    name: 'Sunflower II',
    group: 'universal',
    tone: 'dark',
    swatch: ['#0F0F11', '#D4C227', '#A9C6DC', '#222227'],
  },
  {
    value: 'heliotrope-1',
    name: 'Heliotrope I',
    group: 'variant',
    tone: 'light',
    swatch: ['#F5F5F3', '#B3202C', '#187282', '#EDE8E0'],
  },
  {
    value: 'heliotrope-2',
    name: 'Heliotrope II',
    group: 'variant',
    tone: 'dark',
    swatch: ['#101012', '#C42A3C', '#C9B48A', '#241A1D'],
  },
  {
    value: 'calla-lily',
    name: 'Calla Lily',
    group: 'variant',
    tone: 'light',
    swatch: ['#F7F6F4', '#B5A6EA', '#A8407F', '#ECE8F7'],
  },
  {
    value: 'edelweiss',
    name: 'Edelweiss',
    group: 'variant',
    tone: 'light',
    swatch: ['#F6F6F4', '#F0DC6E', '#5F5F7E', '#EFEBDD'],
  },
  {
    value: 'margaret-sol',
    name: 'Margaret Sol',
    group: 'parallel',
    tone: 'light',
    swatch: ['#F6F5F1', '#C6C94A', '#6F55A0', '#EEEBDC'],
  },
  {
    value: 'margaret-luna',
    name: 'Margaret Luna',
    group: 'parallel',
    tone: 'dark',
    swatch: ['#0F1014', '#5468B0', '#D0CCD4', '#1D2030'],
  },
  {
    value: 'seventh-heaven',
    name: 'Seventh Heaven',
    group: 'thought',
    tone: 'light',
    swatch: ['#F3F4F7', '#6CCFB8', '#262B4D', '#DFF1ED'],
  },
];

export const LIGHT_PALETTES: Set<string> = new Set(
  THEMES.filter((theme) => theme.tone === 'light').map((theme) => theme.value),
);

export const DEFAULT_PALETTE = 'anemone-1';

export const PALETTE_STORAGE_KEY = 'isekai-observatory-palette';

// 未知の値 (廃止テーマ等) は既定テーマに落とす
export const themeByValue = (value: string): Theme =>
  THEMES.find((theme) => theme.value === value) ?? THEMES.find((theme) => theme.value === DEFAULT_PALETTE) ?? THEMES[0];
