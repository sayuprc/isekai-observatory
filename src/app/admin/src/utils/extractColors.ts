import { Vibrant } from 'node-vibrant/browser';

export const SWATCH_ORDER = [
  'Vibrant',
  'Muted',
  'DarkVibrant',
  'DarkMuted',
  'LightVibrant',
  'LightMuted',
] as const;

export type SwatchName = (typeof SWATCH_ORDER)[number];

export interface ColorCandidate {
  name: SwatchName;
  hex: string;
  /** Vibrant の population(画像内での面積の近似) */
  population: number;
  /** 0–1 の彩度 */
  saturation: number;
  /** 彩度×面積のスコア(大きいほど優先) */
  score: number;
}

export interface ExtractedColors {
  candidates: ColorCandidate[];
  defaultHex: string;
}

/** これ未満の彩度は「面積が大きくてもくすみ」扱いで減点する */
const MIN_PREFERRED_SATURATION = 0.22;

/** コントラクト `^#[0-9a-f]{6}$` に揃える */
export const normalizeHex = (value: string): string | null => {
  const trimmed = value.trim().toLowerCase();
  const withHash = trimmed.startsWith('#') ? trimmed : `#${trimmed}`;

  return /^#[0-9a-f]{6}$/.test(withHash) ? withHash : null;
};

const scoreCandidate = (population: number, saturation: number): number => {
  // 彩度を強めに効かせ、面積は対数で伸ばす(極端な背景色だけが勝つのを抑える)
  const chroma = Math.max(saturation, 0.01) ** 1.35;
  const area = Math.log10(population + 10);

  return chroma * area;
};

/**
 * ローカル画像から Vibrant パレットを取る
 * 画像は object URL 経由でブラウザ内だけ読み、呼び出し側が破棄する前提
 * デフォルトは彩度高め×面積大きめのスコア最大
 */
export const extractColorsFromImage = async (file: Blob): Promise<ExtractedColors> => {
  const objectUrl = URL.createObjectURL(file);

  try {
    const palette = await Vibrant.from(objectUrl).quality(1).getPalette();
    const seen = new Set<string>();
    const candidates: ColorCandidate[] = [];

    for (const name of SWATCH_ORDER) {
      const swatch = palette[name];
      if (!swatch) {
        continue;
      }

      const hex = normalizeHex(swatch.hex);
      if (!hex || seen.has(hex)) {
        continue;
      }

      seen.add(hex);
      const saturation = swatch.hsl[1] ?? 0;
      const population = swatch.population;
      candidates.push({
        name,
        hex,
        population,
        saturation,
        score: scoreCandidate(population, saturation),
      });
    }

    if (candidates.length === 0) {
      throw new Error('No color candidates extracted');
    }

    const chromatic = candidates.filter(candidate => candidate.saturation >= MIN_PREFERRED_SATURATION);
    const pool = chromatic.length > 0 ? chromatic : candidates;
    const ranked = [...pool].toSorted((a, b) => b.score - a.score);
    const best = ranked[0];
    if (!best) {
      throw new Error('No color candidates extracted');
    }

    // UI もスコア順(彩度×面積)で並べる
    const ordered = [...candidates].toSorted((a, b) => b.score - a.score);

    return { candidates: ordered, defaultHex: best.hex };
  } finally {
    URL.revokeObjectURL(objectUrl);
  }
};
