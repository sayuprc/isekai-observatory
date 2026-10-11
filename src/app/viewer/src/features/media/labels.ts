export const VIDEO_TYPES = ['mv', 'live-clip', 'interview', 'short'] as const;
export const POST_TYPES = ['tweet', 'instagram', 'youtube-community', 'blog'] as const;

export type VideoType = (typeof VIDEO_TYPES)[number];
export type PostType = (typeof POST_TYPES)[number];

export function isVideo(type: string): type is VideoType {
  return VIDEO_TYPES.includes(type as VideoType);
}

export function isPost(type: string): type is PostType {
  return POST_TYPES.includes(type as PostType);
}

export function mediaTypeLabel(type: string): string {
  return (
    {
      'mv': 'Music Video',
      'live-clip': 'Live Clip',
      'interview': 'Interview',
      'short': 'Short',
      'tweet': 'X / Twitter',
      'instagram': 'Instagram',
      'youtube-community': 'YT Community',
      'blog': 'Blog',
    }[type] ?? type
  );
}

// プラットフォームは状態として保持せず url から導出する(viewer の表示責務)
// バッジ種別は PostCard.astro の platformMeta キーに対応する
export type MediaPlatformKey = 'x' | 'ig' | 'yt' | 'blog';

function hostOf(url: string): string | null {
  try {
    return new URL(url).hostname.toLowerCase().replace(/^www\./, '');
  } catch {
    return null;
  }
}

// url の host からプラットフォームを判定する。未知ホストは blog(その他)に倒す
function mediaPlatformFromUrl(url: string): MediaPlatformKey {
  const host = hostOf(url);

  if (host === null) {
    return 'blog';
  }
  if (host === 'youtu.be' || host === 'youtube.com' || host === 'm.youtube.com' || host.endsWith('.youtube.com')) {
    return 'yt';
  }
  if (host === 'x.com' || host === 'twitter.com' || host.endsWith('.twitter.com')) {
    return 'x';
  }
  if (host === 'instagram.com' || host.endsWith('.instagram.com')) {
    return 'ig';
  }

  return 'blog';
}

// 表示用のプラットフォーム名
export function mediaPlatformLabel(url: string): string {
  return {
    x: 'X',
    ig: 'Instagram',
    yt: 'YouTube',
    blog: 'Web',
  }[mediaPlatformFromUrl(url)];
}

// url から YouTube の動画 ID を抽出する。動画でなければ null
export function youtubeVideoId(url: string): string | null {
  let parsed: URL;
  try {
    parsed = new URL(url);
  } catch {
    return null;
  }

  const host = parsed.hostname.toLowerCase().replace(/^www\./, '');

  if (host === 'youtu.be') {
    return parsed.pathname.slice(1).split('/')[0] || null;
  }
  if (host === 'youtube.com' || host === 'm.youtube.com' || host.endsWith('.youtube.com')) {
    if (parsed.pathname === '/watch') {
      return parsed.searchParams.get('v');
    }

    const matched = parsed.pathname.match(/^\/(?:embed|shorts|live|v)\/([^/?#]+)/);

    return matched ? matched[1] : null;
  }

  return null;
}

/** YouTube サムネイル画像の origin。preconnect 用 */
export const YOUTUBE_THUMBNAIL_ORIGIN = 'https://i.ytimg.com';

// YouTube 動画 url ならサムネイル URL(sddefault.jpg)を導出する。動画でなければ null
export function youtubeThumbnailFromUrl(url: string): string | null {
  const id = youtubeVideoId(url);

  return id === null ? null : `${YOUTUBE_THUMBNAIL_ORIGIN}/vi/${id}/sddefault.jpg`;
}

// 保存済みサムネイル URL(末尾 sddefault.jpg)のファイル名のみを差し替えて
// 解像度バリエーションを srcset として生成する。maxres は欠落しがちなので使わない
export function youtubeThumbnailSrcset(thumbnailUrl: string): string {
  const variants: Array<{ file: string; width: number }> = [
    { file: 'mqdefault.jpg', width: 320 },
    { file: 'hqdefault.jpg', width: 480 },
    { file: 'sddefault.jpg', width: 640 },
  ];

  return variants.map(({ file, width }) => `${thumbnailUrl.replace(/[^/]+$/, file)} ${width}w`).join(', ');
}
