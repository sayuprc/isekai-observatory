// ヰ世界情緒 ファンサイト - データモデル
// 各エンティティをID付き正規化データとして保持し、IDで関係を結ぶ
// すべて架空のダミーデータ(後で差し替え可能)

export type SongCategory = 'オリジナル' | 'カバー';

export type Song = {
  id: string;
  title: string;
  category: SongCategory;
  composer: string;
  lyricist: string;
  arranger: string;
  color: string;
  description: string;
};

export type ReleaseType = 'Single' | 'Album';

export type Release = {
  id: string;
  title: string;
  date: string;
  type: ReleaseType;
  format: string;
  label: string;
  songIds: string[];
  color: string;
  description?: string;
};

export type EventCategory = 'ライブ' | '配信' | 'フェス' | 'メディア' | 'コラボ' | 'リリイベ';
export type EventStatus = '予定' | '終了';

export type AppearanceEvent = {
  id: string;
  date: string;
  title: string;
  venue: string;
  category: EventCategory;
  status: EventStatus;
  description: string;
};

export type Performance = {
  id: string;
  songId: string;
  eventId: string;
  date: string;
  note?: string;
};

export type VideoMediaType = 'mv' | 'live-clip' | 'interview' | 'short';
export type PostMediaType = 'tweet' | 'instagram' | 'youtube-community' | 'blog';
export type MediaType = VideoMediaType | PostMediaType;

export type MediaPlatform = 'x' | 'ig' | 'yt' | 'blog';

// 映像と投稿が同じ配列に同居しているため flat な型で定義する
// 種別は `type` で判別し、種別に応じて使用するフィールドが異なる
export type MediaEntry = {
  id: string;
  type: MediaType;
  date: string;
  songIds: string[];
  eventId: string | null;
  color: string;
  // 映像系で使用
  title?: string;
  views?: string;
  description?: string;
  // 投稿系で使用
  platform?: MediaPlatform;
  text?: string;
  likes?: string;
  reposts?: string;
  hasImage?: boolean;
  readTime?: string;
};

export type NewsCategory = 'LIVE' | 'RELEASE' | 'GOODS' | 'MEDIA';

export type NewsItem = {
  date: string;
  title: string;
  category: NewsCategory;
};

export type Artist = {
  name: string;
  nameEn: string;
  tagline: string;
  debut: string;
};

export type SiteData = {
  artist: Artist;
  songs: Song[];
  releases: Release[];
  events: AppearanceEvent[];
  performances: Performance[];
  media: MediaEntry[];
  news: NewsItem[];
};

export const SITE_DATA: SiteData = {
  artist: {
    name: 'ヰ世界情緒',
    nameEn: 'Isekai Joucho',
    tagline: '歌と詩、夜の深処より',
    debut: '2020.09.05',
  },

  songs: [
    {
      id: 's01',
      title: '夜光の標',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#3a4a72',
      description: '深い夜の海を泳ぐような、静謐なバラード。',
    },
    {
      id: 's02',
      title: '白昼夢の輪郭',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#6b7a99',
      description: '夢と現の境目で揺れる旋律。',
    },
    {
      id: 's03',
      title: '硝子の庭',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#88a4c4',
      description: '壊れやすさと美しさを編み込んだ一曲。',
    },
    {
      id: 's04',
      title: '群青の余白',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#4a5a8a',
      description: '余白に響く青のうた。',
    },
    {
      id: 's05',
      title: '雪明かり',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#9bb5d0',
      description: '雪が照らす、静かな夜の独白。',
    },
    {
      id: 's06',
      title: '燈籠草',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#5a6f9c',
      description: '灯る花の記憶。',
    },
    {
      id: 's07',
      title: '海月のうた',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#3e5577',
      description: 'ゆらゆらと漂う、深海の歌声。',
    },
    {
      id: 's08',
      title: '残響アーカイブ',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#2c3e5e',
      description: 'アルバムタイトル曲。記憶の残響を辿る。',
    },
    {
      id: 's09',
      title: '灰色のワルツ',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#7889a8',
      description: '三拍子で踊る、灰色の詩情。',
    },
    {
      id: 's10',
      title: '星詠みの少女',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#5d72a0',
      description: '星を読み解く少女の物語。',
    },
    {
      id: 's11',
      title: '雨と栞',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#8497b8',
      description: '雨の日に挟む、ささやかな栞。',
    },
    {
      id: 's12',
      title: '鳥籠の天使',
      category: 'カバー',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#465f88',
      description: '歌ってみたカバー楽曲。',
    },
    {
      id: 's13',
      title: '薄明の街',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#324863',
      description: '夜明け前の街を歩く。',
    },
    {
      id: 's14',
      title: '翡翠ノ涙',
      category: 'カバー',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#3d6a73',
      description: '歌ってみたカバー楽曲。',
    },
    {
      id: 's15',
      title: '初めての朝',
      category: 'オリジナル',
      composer: '—',
      lyricist: '—',
      arranger: '—',
      color: '#6e85ac',
      description: 'デビュー楽曲。',
    },
  ],

  releases: [
    {
      id: 'r01',
      title: '夜光の標',
      date: '2024.11.20',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s01'],
      color: '#3a4a72',
    },
    {
      id: 'r02',
      title: '白昼夢の輪郭',
      date: '2024.07.03',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s02'],
      color: '#6b7a99',
    },
    {
      id: 'r03',
      title: '残響アーカイブ',
      date: '2024.03.15',
      type: 'Album',
      format: 'CD / Digital',
      label: '—',
      songIds: ['s03', 's04', 's05', 's06', 's07', 's08'],
      color: '#2c3e5e',
      description: '1stフルアルバム。',
    },
    {
      id: 'r04',
      title: '群青の余白',
      date: '2023.12.08',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s04'],
      color: '#4a5a8a',
    },
    {
      id: 'r05',
      title: '雪明かり',
      date: '2023.10.22',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s05'],
      color: '#9bb5d0',
    },
    {
      id: 'r06',
      title: '燈籠草',
      date: '2023.06.14',
      type: 'Single',
      format: 'CD / Digital',
      label: '—',
      songIds: ['s06'],
      color: '#5a6f9c',
    },
    {
      id: 'r07',
      title: '海月のうた',
      date: '2023.02.28',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s07'],
      color: '#3e5577',
    },
    {
      id: 'r08',
      title: '灰色のワルツ',
      date: '2022.08.07',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s09'],
      color: '#7889a8',
    },
    {
      id: 'r09',
      title: '星詠みの少女',
      date: '2022.04.19',
      type: 'Single',
      format: 'CD / Digital',
      label: '—',
      songIds: ['s10'],
      color: '#5d72a0',
    },
    {
      id: 'r10',
      title: '雨と栞',
      date: '2021.11.03',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s11'],
      color: '#8497b8',
    },
    {
      id: 'r11',
      title: '薄明の街',
      date: '2021.02.14',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s13'],
      color: '#324863',
    },
    {
      id: 'r12',
      title: '初めての朝',
      date: '2020.09.05',
      type: 'Single',
      format: 'Digital',
      label: '—',
      songIds: ['s15'],
      description: 'デビューシングル。',
      color: '#6e85ac',
    },
  ],

  events: [
    {
      id: 'e01',
      date: '2025.03.21',
      title: 'Spring Voyage 2025',
      venue: 'TOKYO GARDEN THEATER',
      category: 'ライブ',
      status: '予定',
      description: '春の単独公演。',
    },
    {
      id: 'e02',
      date: '2024.12.31',
      title: 'Year-End Special Live',
      venue: 'オンライン配信',
      category: '配信',
      status: '終了',
      description: '年越し配信ライブ。',
    },
    {
      id: 'e03',
      date: '2024.10.15',
      title: '幻奏夜会 vol.3',
      venue: 'Zepp DiverCity',
      category: 'ライブ',
      status: '終了',
      description: 'シリーズ第三回。',
    },
    {
      id: 'e04',
      date: '2024.08.04',
      title: 'Summer Music Festival 2024',
      venue: '幕張メッセ',
      category: 'フェス',
      status: '終了',
      description: '夏フェス出演。',
    },
    {
      id: 'e05',
      date: '2024.06.22',
      title: 'Studio Showcase Live',
      venue: '豊洲PIT',
      category: 'ライブ',
      status: '終了',
      description: 'スタジオ合同ライブ。',
    },
    {
      id: 'e06',
      date: '2024.04.10',
      title: 'TV Music Program 出演',
      venue: 'テレビ生放送',
      category: 'メディア',
      status: '終了',
      description: '音楽番組出演。',
    },
    {
      id: 'e07',
      date: '2024.02.14',
      title: 'Valentine Acoustic Night',
      venue: 'Billboard Live TOKYO',
      category: 'ライブ',
      status: '終了',
      description: 'アコースティック編成の特別公演。',
    },
    {
      id: 'e08',
      date: '2023.12.10',
      title: '幻奏夜会 vol.2',
      venue: 'LINE CUBE SHIBUYA',
      category: 'ライブ',
      status: '終了',
      description: 'シリーズ第二回。',
    },
    {
      id: 'e09',
      date: '2023.09.30',
      title: 'Anime Tie-up Event',
      venue: '東京国際フォーラム',
      category: 'コラボ',
      status: '終了',
      description: 'アニメタイアップ記念イベント。',
    },
    {
      id: 'e10',
      date: '2023.07.15',
      title: 'Summer Online Live',
      venue: 'オンライン配信',
      category: '配信',
      status: '終了',
      description: '夏の配信ライブ。',
    },
    {
      id: 'e11',
      date: '2023.05.20',
      title: '幻奏夜会 vol.1',
      venue: 'TSUTAYA O-EAST',
      category: 'ライブ',
      status: '終了',
      description: 'シリーズ初回公演。',
    },
    {
      id: 'e12',
      date: '2023.02.18',
      title: 'Studio Anniversary Live',
      venue: '国立代々木競技場',
      category: 'ライブ',
      status: '終了',
      description: 'スタジオ周年ライブ。',
    },
    {
      id: 'e13',
      date: '2022.11.23',
      title: 'Album Release Party',
      venue: 'TSUTAYA O-WEST',
      category: 'リリイベ',
      status: '終了',
      description: 'アルバム『残響アーカイブ』リリース記念。',
    },
    {
      id: 'e14',
      date: '2022.08.27',
      title: 'Online Birthday Live',
      venue: 'オンライン配信',
      category: '配信',
      status: '終了',
      description: '誕生日記念配信。',
    },
    {
      id: 'e15',
      date: '2022.05.04',
      title: 'GW Special Stream',
      venue: 'オンライン配信',
      category: '配信',
      status: '終了',
      description: 'GW特別配信。',
    },
  ],

  performances: [
    { id: 'p01', songId: 's01', eventId: 'e02', date: '2024.12.31', note: '新曲披露' },
    { id: 'p02', songId: 's01', eventId: 'e03', date: '2024.10.15' },
    { id: 'p03', songId: 's08', eventId: 'e02', date: '2024.12.31', note: 'アルバム代表曲' },
    { id: 'p04', songId: 's08', eventId: 'e03', date: '2024.10.15' },
    { id: 'p05', songId: 's08', eventId: 'e08', date: '2023.12.10' },
    { id: 'p06', songId: 's08', eventId: 'e12', date: '2023.02.18' },
    { id: 'p07', songId: 's08', eventId: 'e13', date: '2022.11.23' },
    { id: 'p08', songId: 's03', eventId: 'e03', date: '2024.10.15' },
    { id: 'p09', songId: 's03', eventId: 'e07', date: '2024.02.14', note: 'アコースティック編成' },
    { id: 'p10', songId: 's04', eventId: 'e02', date: '2024.12.31' },
    { id: 'p11', songId: 's04', eventId: 'e08', date: '2023.12.10' },
    { id: 'p12', songId: 's05', eventId: 'e02', date: '2024.12.31' },
    { id: 'p13', songId: 's06', eventId: 'e11', date: '2023.05.20' },
    { id: 'p14', songId: 's06', eventId: 'e08', date: '2023.12.10' },
    { id: 'p15', songId: 's07', eventId: 'e10', date: '2023.07.15' },
    { id: 'p16', songId: 's09', eventId: 'e07', date: '2024.02.14' },
    { id: 'p17', songId: 's10', eventId: 'e09', date: '2023.09.30', note: 'タイアップ楽曲' },
    { id: 'p18', songId: 's10', eventId: 'e12', date: '2023.02.18' },
    { id: 'p19', songId: 's11', eventId: 'e07', date: '2024.02.14' },
    { id: 'p20', songId: 's13', eventId: 'e11', date: '2023.05.20' },
    { id: 'p21', songId: 's15', eventId: 'e02', date: '2024.12.31', note: 'デビュー曲' },
    { id: 'p22', songId: 's15', eventId: 'e12', date: '2023.02.18' },
    { id: 'p23', songId: 's15', eventId: 'e14', date: '2022.08.27' },
    { id: 'p24', songId: 's12', eventId: 'e10', date: '2023.07.15', note: 'カバー' },
    { id: 'p25', songId: 's14', eventId: 'e15', date: '2022.05.04', note: 'カバー' },
    { id: 'p26', songId: 's02', eventId: 'e04', date: '2024.08.04' },
    { id: 'p27', songId: 's02', eventId: 'e05', date: '2024.06.22' },
    { id: 'p28', songId: 's01', eventId: 'e01', date: '2025.03.21', note: '予定' },
    { id: 'p29', songId: 's08', eventId: 'e01', date: '2025.03.21', note: '予定' },
    { id: 'p30', songId: 's03', eventId: 'e01', date: '2025.03.21', note: '予定' },
  ],

  media: [
    {
      id: 'm01',
      type: 'mv',
      title: '夜光の標 [Music Video]',
      date: '2024.11.20',
      views: '1.2M',
      songIds: ['s01'],
      eventId: null,
      color: '#3a4a72',
      description: '深海の中、光を辿る情緒。',
    },
    {
      id: 'm02',
      type: 'mv',
      title: '白昼夢の輪郭 [Music Video]',
      date: '2024.07.03',
      views: '892K',
      songIds: ['s02'],
      eventId: null,
      color: '#6b7a99',
      description: '白い部屋、揺れるカーテン、夢の輪郭。',
    },
    {
      id: 'm03',
      type: 'mv',
      title: '硝子の庭 [Music Video]',
      date: '2024.03.15',
      views: '2.1M',
      songIds: ['s03'],
      eventId: null,
      color: '#88a4c4',
      description: '硝子細工に囲まれた庭園のシーン。',
    },
    {
      id: 'm04',
      type: 'mv',
      title: '群青の余白 [Music Video]',
      date: '2023.12.08',
      views: '1.5M',
      songIds: ['s04'],
      eventId: null,
      color: '#4a5a8a',
      description: '群青に染まる紙、書かれない手紙。',
    },
    {
      id: 'm05',
      type: 'mv',
      title: '雪明かり [Music Video]',
      date: '2023.10.22',
      views: '780K',
      songIds: ['s05'],
      eventId: null,
      color: '#9bb5d0',
      description: '雪夜の幻想、ろうそくの灯。',
    },
    {
      id: 'm06',
      type: 'mv',
      title: '燈籠草 [Music Video]',
      date: '2023.06.14',
      views: '1.1M',
      songIds: ['s06'],
      eventId: null,
      color: '#5a6f9c',
      description: '灯籠の咲く野原を歩く。',
    },
    {
      id: 'm07',
      type: 'mv',
      title: '海月のうた [Music Video]',
      date: '2023.02.28',
      views: '950K',
      songIds: ['s07'],
      eventId: null,
      color: '#3e5577',
      description: '深海と海月のアニメーション。',
    },
    {
      id: 'm08',
      type: 'mv',
      title: '残響アーカイブ [Music Video]',
      date: '2022.11.11',
      views: '3.4M',
      songIds: ['s08'],
      eventId: null,
      color: '#2c3e5e',
      description: '代表曲MV。膨大な書架と記憶。',
    },
    {
      id: 'm09',
      type: 'mv',
      title: '灰色のワルツ [Music Video]',
      date: '2022.08.07',
      views: '680K',
      songIds: ['s09'],
      eventId: null,
      color: '#7889a8',
      description: 'モノクロ、踊る人影。',
    },
    {
      id: 'm10',
      type: 'mv',
      title: '星詠みの少女 [Music Video]',
      date: '2022.04.19',
      views: '1.8M',
      songIds: ['s10'],
      eventId: null,
      color: '#5d72a0',
      description: '天文台、星図、少女。',
    },
    {
      id: 'm11',
      type: 'mv',
      title: '雨と栞 [Music Video]',
      date: '2021.11.03',
      views: '590K',
      songIds: ['s11'],
      eventId: null,
      color: '#8497b8',
      description: '雨音と本のページ。',
    },
    {
      id: 'm12',
      type: 'mv',
      title: '薄明の街 [Music Video]',
      date: '2021.02.14',
      views: '720K',
      songIds: ['s13'],
      eventId: null,
      color: '#324863',
      description: '夜明けの街並みを歩く実写風。',
    },
    {
      id: 'm13',
      type: 'live-clip',
      title: '残響アーカイブ — 幻奏夜会 vol.2 LIVE',
      date: '2023.12.20',
      views: '320K',
      songIds: ['s08'],
      eventId: 'e08',
      color: '#2c3e5e',
      description: 'ライブ切り抜き映像。',
    },
    {
      id: 'm14',
      type: 'live-clip',
      title: '硝子の庭 — Valentine Acoustic Night',
      date: '2024.02.20',
      views: '180K',
      songIds: ['s03'],
      eventId: 'e07',
      color: '#88a4c4',
      description: 'アコースティック編成のライブ映像。',
    },
    {
      id: 'm15',
      type: 'interview',
      title: 'Album『残響アーカイブ』インタビュー',
      date: '2022.11.18',
      views: '85K',
      songIds: ['s08', 's03', 's04'],
      eventId: 'e13',
      color: '#2c3e5e',
      description: 'アルバム制作秘話。',
    },
    {
      id: 'm16',
      type: 'short',
      title: '夜光の標 — 30秒トレーラー',
      date: '2024.11.15',
      views: '210K',
      songIds: ['s01'],
      eventId: null,
      color: '#3a4a72',
      description: 'リリース告知用の短尺。',
    },
    {
      id: 'm17',
      type: 'live-clip',
      title: '幻奏夜会 vol.3 ダイジェスト',
      date: '2024.10.25',
      views: '450K',
      songIds: ['s01', 's03', 's08'],
      eventId: 'e03',
      color: '#3a4a72',
      description: '公演ダイジェスト映像。',
    },
    {
      id: 'p01',
      type: 'tweet',
      platform: 'x',
      date: '2025.01.28',
      songIds: ['s01'],
      eventId: null,
      color: '#3a4a72',
      text: '新曲『夜光の標』、本日0:00より各種ストリーミングサービスにて配信スタートしました。深い夜の海にひとつだけ灯る光のような曲です。聴いてくれた人の夜が、すこしだけやわらかくなりますように。',
      likes: '32.4K',
      reposts: '8.1K',
    },
    {
      id: 'p02',
      type: 'instagram',
      platform: 'ig',
      date: '2024.12.31',
      songIds: ['s08', 's15'],
      eventId: null,
      color: '#2c3e5e',
      text: '今年もありがとうございました。年越し配信、これから。',
      likes: '18.2K',
      hasImage: true,
    },
    {
      id: 'p03',
      type: 'tweet',
      platform: 'x',
      date: '2024.11.15',
      songIds: ['s01'],
      eventId: null,
      color: '#3a4a72',
      text: '11/20リリース『夜光の標』MV、ティザー公開しました。撮影は冬の日本海で。雪と波と、あとちょっとの祈り。',
      likes: '21.8K',
      reposts: '5.2K',
    },
    {
      id: 'p04',
      type: 'youtube-community',
      platform: 'yt',
      date: '2024.10.20',
      songIds: [],
      eventId: null,
      color: '#5a6f9c',
      text: '幻奏夜会 vol.3、来てくれた皆さん、配信で見てくれた皆さん、本当にありがとうございました。次は春に会いましょう。',
      likes: '12.5K',
    },
    {
      id: 'p05',
      type: 'tweet',
      platform: 'x',
      date: '2024.07.03',
      songIds: ['s02'],
      eventId: null,
      color: '#6b7a99',
      text: '『白昼夢の輪郭』MV、本日18:00プレミア公開です。白い部屋で見た夢の話。',
      likes: '15.3K',
      reposts: '3.8K',
    },
    {
      id: 'p06',
      type: 'blog',
      platform: 'blog',
      date: '2024.04.02',
      songIds: ['s08'],
      eventId: null,
      color: '#2c3e5e',
      title: 'アルバムから一年',
      text: '『残響アーカイブ』をリリースしてから一年が経ちました。あのとき書いた曲たちが、今もどこかで誰かの夜を照らしているのなら、それ以上のことはありません。最近のことを少しだけ。',
      readTime: '3 min',
    },
    {
      id: 'p07',
      type: 'instagram',
      platform: 'ig',
      date: '2024.02.14',
      songIds: ['s03', 's09'],
      eventId: 'e07',
      color: '#88a4c4',
      text: 'Billboard Live、おわり。アコースティックの夜でした。',
      likes: '9.7K',
      hasImage: true,
    },
    {
      id: 'p08',
      type: 'tweet',
      platform: 'x',
      date: '2023.12.20',
      songIds: ['s08'],
      eventId: 'e08',
      color: '#2c3e5e',
      text: 'vol.2 終演しました。最後の『残響アーカイブ』、客席のペンライトが本当に綺麗で。',
      likes: '24.1K',
      reposts: '6.5K',
    },
  ],

  news: [
    { date: '2025.02.10', title: 'Spring Voyage 2025 チケット先行受付開始', category: 'LIVE' },
    { date: '2025.01.28', title: '新曲『夜光の標』ストリーミング配信開始', category: 'RELEASE' },
    { date: '2025.01.15', title: '公式グッズ新作発売のお知らせ', category: 'GOODS' },
    { date: '2024.12.20', title: '年越し配信ライブ詳細決定', category: 'LIVE' },
    { date: '2024.12.05', title: 'メディア出演情報を更新', category: 'MEDIA' },
  ],
};

// ─── リレーショナルアクセサ ───
export type SongStats = {
  releases: number;
  performances: number;
  media: number;
  events: number;
};

export type EventStats = {
  songs: number;
  media: number;
};

export const Q = {
  song: (id: string): Song | undefined => SITE_DATA.songs.find(s => s.id === id),
  event: (id: string): AppearanceEvent | undefined => SITE_DATA.events.find(e => e.id === id),
  release: (id: string): Release | undefined => SITE_DATA.releases.find(r => r.id === id),
  media: (id: string): MediaEntry | undefined => SITE_DATA.media.find(m => m.id === id),
  performance: (id: string): Performance | undefined => SITE_DATA.performances.find(p => p.id === id),

  releasesOfSong: (songId: string): Release[] => SITE_DATA.releases.filter(r => r.songIds.includes(songId)),
  performancesOfSong: (songId: string): Performance[] => SITE_DATA.performances.filter(p => p.songId === songId),
  mediaOfSong: (songId: string): MediaEntry[] => SITE_DATA.media.filter(m => m.songIds.includes(songId)),
  eventsOfSong: (songId: string): AppearanceEvent[] => {
    const ids = [...new Set(SITE_DATA.performances.filter(p => p.songId === songId).map(p => p.eventId))];
    return ids.map(id => Q.event(id)).filter((e): e is AppearanceEvent => e !== undefined);
  },
  firstReleaseDateOfSong: (songId: string): string | null => {
    const rs = Q.releasesOfSong(songId);
    if (!rs.length) return null;
    return rs.map(r => r.date).sort()[0] ?? null;
  },
  firstPerformanceDateOfSong: (songId: string): string | null => {
    const ps = Q.performancesOfSong(songId);
    if (!ps.length) return null;
    return ps.map(p => p.date).sort()[0] ?? null;
  },
  firstAppearanceDateOfSong: (songId: string): string | null => {
    const dates = [Q.firstReleaseDateOfSong(songId), Q.firstPerformanceDateOfSong(songId)].filter(
      (d): d is string => d !== null,
    );
    if (!dates.length) return null;
    return dates.sort()[0] ?? null;
  },

  performancesAtEvent: (eventId: string): Performance[] => SITE_DATA.performances.filter(p => p.eventId === eventId),
  songsAtEvent: (eventId: string): Song[] => {
    const ids = [...new Set(SITE_DATA.performances.filter(p => p.eventId === eventId).map(p => p.songId))];
    return ids.map(id => Q.song(id)).filter((s): s is Song => s !== undefined);
  },
  mediaOfEvent: (eventId: string): MediaEntry[] => SITE_DATA.media.filter(m => m.eventId === eventId),

  songsOfMedia: (mediaId: string): Song[] => {
    const m = Q.media(mediaId);
    if (!m) return [];
    return m.songIds.map(id => Q.song(id)).filter((s): s is Song => s !== undefined);
  },
  eventOfMedia: (mediaId: string): AppearanceEvent | null => {
    const m = Q.media(mediaId);
    return m && m.eventId ? (Q.event(m.eventId) ?? null) : null;
  },

  songsOfRelease: (releaseId: string): Song[] => {
    const r = Q.release(releaseId);
    if (!r) return [];
    return r.songIds.map(id => Q.song(id)).filter((s): s is Song => s !== undefined);
  },

  songStats: (songId: string): SongStats => ({
    releases: Q.releasesOfSong(songId).length,
    performances: Q.performancesOfSong(songId).length,
    media: Q.mediaOfSong(songId).length,
    events: Q.eventsOfSong(songId).length,
  }),
  eventStats: (eventId: string): EventStats => ({
    songs: Q.songsAtEvent(eventId).length,
    media: Q.mediaOfEvent(eventId).length,
  }),
};
