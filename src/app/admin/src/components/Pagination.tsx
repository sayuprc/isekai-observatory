import { For } from 'solid-js';
import { pageItems } from '../utils/pagination';

interface Props {
  page: number;
  maxPage: number;
  onChange: (page: number) => void;
}

export const Pagination = (props: Props) => {
  return (
    <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
      <div class="join">
        <button
          type="button"
          class="btn join-item btn-sm"
          disabled={props.page <= 1}
          onClick={() => props.onChange(props.page - 1)}
        >
          前へ
        </button>
        <For each={pageItems(props.page, props.maxPage)}>
          {(item) =>
            item === 'ellipsis' ? (
              <button type="button" class="btn join-item btn-sm btn-disabled" tabIndex={-1}>
                …
              </button>
            ) : item === props.page ? (
              // disabled にすると灰色になり現在地が目立たなくなるため、色を保ったまま押せなくする
              <button
                type="button"
                class="btn join-item btn-sm btn-primary pointer-events-none"
                aria-current="page"
                aria-disabled="true"
              >
                {item}
              </button>
            ) : (
              <button type="button" class="btn join-item btn-sm" onClick={() => props.onChange(item)}>
                {item}
              </button>
            )
          }
        </For>
        <button
          type="button"
          class="btn join-item btn-sm"
          disabled={props.page >= props.maxPage}
          onClick={() => props.onChange(props.page + 1)}
        >
          次へ
        </button>
      </div>
    </div>
  );
};
