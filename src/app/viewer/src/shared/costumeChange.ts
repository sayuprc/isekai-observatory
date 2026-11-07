import type { Theme } from './themes';

export const PETALS_STORAGE_KEY = 'isekai-observatory-theme-petals';

// 保存値は 'off' のときだけ無効 未保存や読み取り失敗は有効として扱う
export const isPetalsEnabled = () => {
  try {
    return localStorage.getItem(PETALS_STORAGE_KEY) !== 'off';
  } catch {
    return true;
  }
};

export const setPetalsEnabled = (enabled: boolean) => {
  try {
    localStorage.setItem(PETALS_STORAGE_KEY, enabled ? 'on' : 'off');
  } catch {
    // 保存できなくても、そのセッションの間は切り替え済みの状態で動く
  }
};

const PETAL_COUNT = 140;
const SAMPLES = 72;
const TAU = Math.PI * 2;
// 二重螺旋の本数と、螺旋が画面を回る速さ(1 本が 3.2 秒で TURNS 周する速さ)
const STRANDS = 2;
const TURNS = 2.2;
const OMEGA = (TURNS * TAU) / 3200;
// 1 枚が下から画面の上寄りまで舞い上がる時間
const RISE_MS = 1500;
// 出発のずれ 先に着いた花びらが、上寄りで輪を作って待つ時間にあたる
const TRAIL_MS = 350;
// 輪になってから画面の下へ抜けるまでの時間
const FALL_MS = 1500;
const TOTAL_MS = RISE_MS + TRAIL_MS + FALL_MS;
// 輪になる(最後の花びらが上寄りに着く)時刻
const RING_START_MS = RISE_MS + TRAIL_MS;

const between = (min: number, max: number) => min + Math.random() * (max - min);
const lerp = (from: number, to: number, ratio: number) => from + (to - from) * ratio;
const clamp = (value: number) => Math.min(1, Math.max(0, value));
const easeOut = (ratio: number) => 1 - (1 - ratio) ** 3;
const easeInOut = (ratio: number) => ratio * ratio * (3 - 2 * ratio);
const easeIn = (ratio: number) => ratio ** 2.2;

// 新しいテーマの配色の花びらを、画面中央の縦軸のまわりで舞わせる
// 下から二重螺旋で舞い上がり、上寄りで 1 つの輪になって、広がりながら下へ抜ける
// 要素は動き終わったら自分で片付けるので、呼び出し側は後始末をしなくてよい
const scatterPetals = (theme: Theme) => {
  document.querySelector('.petal-layer')?.remove();

  const layer = document.createElement('div');
  layer.className = 'petal-layer';
  layer.setAttribute('aria-hidden', 'true');

  const { innerWidth: width, innerHeight: height } = window;
  const centerX = width / 2;
  const maxRadius = Math.min(width * 0.42, 420);
  const peakY = height * 0.12;
  const bottomY = height + 24;
  // 主役の色を多めにして、2 色目を差し色にする
  const colors = [theme.swatch[1], theme.swatch[1], theme.swatch[2]];
  // 渦の向きは全員そろえる
  const spinDirection = Math.random() < 0.5 ? -1 : 1;
  const perStrand = PETAL_COUNT / STRANDS;
  const animations: Animation[] = [];

  for (let index = 0; index < PETAL_COUNT; index += 1) {
    const petal = document.createElement('span');
    const size = between(10, 18);
    // 軸(螺旋と輪)の近くに、半径と高さを散らす
    const radius = between(0.8, 1.12) * maxRadius;
    const lift = between(-1, 1) * height * 0.05;
    const wobblePhase = between(0, TAU);
    // 本ごとに位相を半周ずらし、少しのゆらぎで整いすぎないようにする
    const strand = index % STRANDS;
    const phase = (strand / STRANDS) * TAU + between(-0.22, 0.22);
    // 1 本の中での並び順(0 から 1 未満)
    const rank = Math.floor(index / STRANDS) / perStrand;
    const delay = rank * TRAIL_MS;

    petal.className = 'petal';
    petal.style.setProperty('--petal-color', colors[index % colors.length]);
    petal.style.setProperty('--size', `${size}px`);

    const keyframes = Array.from({ length: SAMPLES + 1 }, (_, step) => {
      const time = (step / SAMPLES) * TOTAL_MS;
      // 自分の出発からの経過時間 出発前は負になり、見えない
      const local = time - delay;
      let y = bottomY;
      let spread = 1;

      if (time >= RING_START_MS) {
        // 輪になったあとは全員が同じ高さで、同じだけ広がりながら下がる
        const fall = (time - RING_START_MS) / FALL_MS;
        y = lerp(peakY, height + 48, easeIn(fall));
        spread = 1 + fall * 0.6;
      } else if (local >= RISE_MS) {
        y = peakY;
      } else if (local > 0) {
        y = lerp(bottomY, peakY, easeOut(local / RISE_MS));
      }

      // 上るあいだは下ほど絞った渦にする 輪になって下がるときは絞らず、そのまま広げる
      const climbed = time >= RING_START_MS ? 1 : clamp((height - y) / height);
      // 舞い上がりの後半で、2 本の筋のずれを半周ずつ(合わせて一周)に開いて、輪の上に等間隔で並べる
      const unwind = easeInOut(clamp((local - RISE_MS * 0.5) / (RISE_MS * 0.5)));
      const slotOffset = lerp(OMEGA * TRAIL_MS, Math.PI, unwind);
      const angle = phase + spinDirection * (OMEGA * time - slotOffset * rank);
      const orbit = radius * (0.25 + 0.75 * Math.min(1, climbed * 1.3)) * spread;
      // 手前(sin が正)を大きく濃く、奥を小さく薄くして奥行きを出す
      const depth = (Math.sin(angle) + 1) / 2;
      const scale = 0.7 + 0.5 * depth;
      const flutter = 0.6 + 0.4 * Math.abs(Math.cos(angle * 2));
      // 軸のまわりで小さく揺れて、整列しすぎないようにする
      const wobbleX = Math.sin(time / 260 + wobblePhase) * 9;
      const wobbleY = Math.cos(time / 310 + wobblePhase) * 7;
      const fade = clamp(local / 180) * clamp((TOTAL_MS - time) / 180);

      return {
        transform: `translate3d(${centerX + orbit * Math.cos(angle) + wobbleX}px, ${y + lift + wobbleY}px, 0) rotate(${(angle * 180) / Math.PI / 2}deg) scale(${scale * flutter}, ${scale})`,
        opacity: fade * (0.5 + 0.5 * depth),
      };
    });

    layer.append(petal);
    animations.push(petal.animate(keyframes, { duration: TOTAL_MS, easing: 'linear', fill: 'both' }));
  }

  document.body.append(layer);
  void Promise.allSettled(animations.map((animation) => animation.finished)).then(() => layer.remove());
};

// 画面の下から上へ向かって新しいテーマに着替え、同じ動きで花びらが舞い上がって落ちる
// 着替えは View Transitions の切り抜きで見せる 使えない環境や動きを減らす設定では、その場で切り替える
export const playCostumeChange = (update: () => void, theme: Theme) => {
  if (!document.startViewTransition || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    update();
    return;
  }

  document.startViewTransition(() => {
    update();

    // 花びらは設定で止められる 着替え自体は止めない
    if (isPetalsEnabled()) {
      // 変えたあとの画面に置くことで、旧画面の撮影には写り込まない
      scatterPetals(theme);
    }
  });
};
