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
        <button
          type="button"
          class="btn join-item btn-sm"
          disabled={props.page >= props.maxPage}
          onClick={() => props.onChange(props.page + 1)}
        >
          次へ
        </button>
      </div>
      <span class="text-sm text-base-content/70">
        {props.page} / {props.maxPage}
      </span>
    </div>
  );
};
