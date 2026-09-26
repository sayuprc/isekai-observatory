import type { JSX } from 'solid-js';

const MusicNoteIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z"
    />
  </svg>
);

const TagIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"
    />
    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
  </svg>
);

const UsersIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
    />
  </svg>
);

const SwatchIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M4.098 19.902a3.75 3.75 0 0 0 5.304 0l6.401-6.402M6.75 21A3.75 3.75 0 0 1 3 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 0 0 3.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008Z"
    />
  </svg>
);

const PlayIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M5.25 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.713 1.295 2.573 0 3.286L8.03 19.99c-1.25.688-2.779-.216-2.779-1.643V5.653Z"
    />
  </svg>
);

const DiscIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM12 12h.008v.008H12V12Zm0 0A2.25 2.25 0 1 0 12 16.5 2.25 2.25 0 0 0 12 12Z"
    />
  </svg>
);

const ShieldIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"
    />
  </svg>
);

const MapPinIcon = () => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    stroke-width="1.5"
    stroke="currentColor"
    class="size-5"
  >
    <path
      stroke-linecap="round"
      stroke-linejoin="round"
      d="M12 21s7.5-4.35 7.5-11.25a7.5 7.5 0 1 0-15 0C4.5 16.65 12 21 12 21Z"
    />
    <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
  </svg>
);

type NavItem = {
  href: string;
  label: string;
  icon: () => JSX.Element;
};

type NavSection = {
  title: string;
  items: NavItem[];
};

const navSections: NavSection[] = [
  {
    title: '楽曲',
    items: [
      { href: '/songs', label: '楽曲', icon: MusicNoteIcon },
      { href: '/release-groups', label: 'リリースグループ', icon: DiscIcon },
      { href: '/media', label: 'メディア', icon: PlayIcon },
      { href: '/song-types', label: '楽曲種別', icon: TagIcon },
      { href: '/song-tags', label: '楽曲タグ', icon: SwatchIcon },
    ],
  },
  {
    title: '関係者',
    items: [{ href: '/persons', label: '人物', icon: UsersIcon }],
  },
  {
    title: 'イベント',
    items: [
      { href: '/events', label: 'イベント', icon: PlayIcon },
      { href: '/venues', label: '開催先', icon: MapPinIcon },
    ],
  },
  {
    title: '管理',
    items: [
      { href: '/admin-users', label: '管理ユーザー', icon: ShieldIcon },
      { href: '/recovery-codes', label: 'リカバリーコード', icon: ShieldIcon },
      { href: '/audit-logs', label: '監査ログ', icon: ShieldIcon },
    ],
  },
];

interface Props {
  currentPath: string;
}

const isActivePath = (currentPath: string, href: string): boolean => {
  if (href === '/') {
    return currentPath === href;
  }

  return currentPath === href || currentPath.startsWith(`${href}/`);
};

export const Sidebar = (props: Props) => {
  return (
    <aside class="bg-base-200 flex min-h-full w-56 flex-col border-r border-base-300">
      <div class="border-b border-base-300 px-4 py-5">
        <a href="/" class="text-lg font-bold">
          ヰ世界観測所
        </a>
      </div>
      <nav class="flex-1 overflow-y-auto px-2 py-4">
        {navSections.map((section) => (
          <div class="mb-3">
            <h2 class="px-3 py-1 text-xs font-semibold tracking-wider text-base-content/60">{section.title}</h2>
            <ul class="menu menu-sm gap-1">
              {section.items.map((item) => (
                <li>
                  <a
                    href={item.href}
                    class={
                      isActivePath(props.currentPath, item.href)
                        ? 'active border-l-4 border-primary pl-[calc(theme(spacing.3)-4px)] font-semibold'
                        : 'border-l-4 border-transparent'
                    }
                    aria-current={isActivePath(props.currentPath, item.href) ? 'page' : undefined}
                  >
                    {item.icon()}
                    {item.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>
        ))}
      </nav>
    </aside>
  );
};
