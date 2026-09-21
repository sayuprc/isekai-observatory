interface LoadingProps {
  state: 'loading';
  colSpan: number;
}

interface EmptyProps {
  state: 'empty';
  colSpan: number;
  message?: string;
}

interface ErrorProps {
  state: 'error';
  colSpan: number;
  message: string;
  onRetry: () => void;
}

type Props = LoadingProps | EmptyProps | ErrorProps;

export const ListState = (props: Props) => {
  if (props.state === 'loading') {
    return (
      <tr>
        <td colspan={props.colSpan} class="py-10">
          <div class="flex items-center justify-center gap-3 text-base-content/70" role="status" aria-live="polite">
            <span class="loading loading-spinner loading-md" aria-hidden="true" />
            <span>読み込み中...</span>
          </div>
        </td>
      </tr>
    );
  }

  if (props.state === 'error') {
    return (
      <tr>
        <td colspan={props.colSpan} class="py-10">
          <div class="flex flex-col items-center justify-center gap-3 text-center">
            <p class="text-error">{props.message}</p>
            <button type="button" class="btn btn-outline btn-sm" onClick={props.onRetry}>
              再試行
            </button>
          </div>
        </td>
      </tr>
    );
  }

  return (
    <tr>
      <td colspan={props.colSpan} class="py-10 text-center text-base-content/70">
        {props.message ?? '該当するデータはありません'}
      </td>
    </tr>
  );
};
