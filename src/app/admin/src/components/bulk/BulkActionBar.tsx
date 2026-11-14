import { createSignal, Show } from 'solid-js';
import type { RequestTag } from '../../generated';
import { createSubmitting } from '../../utils/use-submitting';
import { SegmentedControl } from '../SegmentedControl';
import { TagPicker } from '../tag/TagPicker';
import type { BulkSelection } from './bulk-selection';
import { bulkUpdate, type BulkTargetKey } from './bulk-targets';

const DISPLAY_CHOICES = [
  { value: 'keep', label: '変更しない' },
  { value: 'true', label: '公開' },
  { value: 'false', label: '非公開' },
];

type DisplayChoice = 'keep' | 'true' | 'false';

interface Props {
  target: BulkTargetKey;
  // 確認ダイアログに出す対象の呼び名 (例: 楽曲)
  label: string;
  selection: BulkSelection;
  // 更新に成功したあとに呼ぶ。一覧の再取得に使う
  onDone: () => void;
}

// 一覧で選んだ記録に、タグの付与・除去と公開設定の切り替えをまとめて行う
export const BulkActionBar = (props: Props) => {
  const [addTags, setAddTags] = createSignal<RequestTag[]>([]);
  const [removeTags, setRemoveTags] = createSignal<RequestTag[]>([]);
  const [displayChoice, setDisplayChoice] = createSignal<DisplayChoice>('keep');
  const { isSubmitting, withSubmitting } = createSubmitting();
  const [resultMessage, setResultMessage] = createSignal<string | null>(null);
  const [errorMessage, setErrorMessage] = createSignal<string | null>(null);

  const hasOperation = () => addTags().length > 0 || removeTags().length > 0 || displayChoice() !== 'keep';

  const summary = () => {
    const parts: string[] = [];
    if (addTags().length > 0) parts.push(`タグ ${addTags().length} 件を付与`);
    if (removeTags().length > 0) parts.push(`タグ ${removeTags().length} 件を除去`);
    if (displayChoice() !== 'keep') parts.push(displayChoice() === 'true' ? '公開にする' : '非公開にする');
    return parts.join('、');
  };

  const execute = withSubmitting(async () => {
    setResultMessage(null);
    setErrorMessage(null);

    if (addTags().some((added) => removeTags().some((removed) => removed.tagId === added.tagId))) {
      setErrorMessage('同じタグを付与と除去の両方には指定できません');
      return;
    }

    if (!window.confirm(`${props.selection.count()} 件の${props.label}に、${summary()}を行います。よろしいですか？`)) {
      return;
    }

    const result = await bulkUpdate(props.target, {
      ids: props.selection.ids(),
      addTagIds: addTags().map((entry) => entry.tagId),
      removeTagIds: removeTags().map((entry) => entry.tagId),
      isDisplay: displayChoice() === 'keep' ? undefined : displayChoice() === 'true',
    });

    if (!result.ok) {
      setErrorMessage(result.message);
      return;
    }

    setResultMessage(`${result.updatedCount} 件の${props.label}を更新しました`);
    props.selection.clear();
    setAddTags([]);
    setRemoveTags([]);
    setDisplayChoice('keep');
    props.onDone();
  });

  return (
    <>
      <div>
        <section class="mb-4 space-y-4 rounded-box border border-primary/40 bg-base-200 p-4" aria-label="一括操作">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="font-semibold">{props.selection.count()} 件を選択中</p>
            <div class="flex gap-2">
              <button type="button" class="btn btn-ghost btn-sm" onClick={() => props.selection.clear()}>
                選択を解除
              </button>
              <button
                type="button"
                class="btn btn-primary btn-sm"
                disabled={isSubmitting() || !hasOperation()}
                onClick={() => void execute()}
              >
                {isSubmitting() ? '実行中...' : '一括で実行'}
              </button>
            </div>
          </div>
          <div class="grid gap-4 lg:grid-cols-2">
            <TagPicker label="付与するタグ" tags={addTags} setTags={setAddTags} />
            <TagPicker label="除去するタグ" tags={removeTags} setTags={setRemoveTags} />
          </div>
          <div>
            <p class="mb-2 text-sm font-semibold text-base-content/70">公開設定</p>
            <SegmentedControl
              label="公開設定"
              options={DISPLAY_CHOICES}
              value={displayChoice()}
              onChange={(value) => setDisplayChoice(value as DisplayChoice)}
            />
          </div>
          <Show when={errorMessage()}>{(message) => <p class="text-sm text-error">{message()}</p>}</Show>
        </section>
      </div>
      <Show when={resultMessage()}>{(message) => <p class="mb-4 text-sm text-success">{message()}</p>}</Show>
    </>
  );
};
