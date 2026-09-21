import "@typespec/http";
import "../../shared";
import "./domain.tsp";

using IsekaiObservatory.Shared;

namespace IsekaiObservatory.Viewer;

@example(#{
  releaseGroups: #[
    #{
      releaseGroupId: "0f2185cd-b4ab-4f22-b0a3-fd93cbbd2df2",
      title: "テストアルバム",
      type: #{ name: "アルバム", value: ReleaseGroupTypeValue.Album },
      description: "1st アルバム",
      firstReleasedOn: "2026-05-01",
      releases: #[
        #{
          releaseId: "8b53bfbc-c4be-4276-9d92-e2ee181e2b4f",
          name: "初回限定盤",
          releasedOn: "2026-05-01",
          description: "",
          color: "#4a5a78",
          orderNo: 1,
          formats: #[#{ name: "CD", value: ReleaseFormatValue.Cd }],
          media: #[
            #{
              position: 1,
              name: null,
              tracks: #[
                #{ trackNo: 1, songId: "3cd42c09-ff3c-4cd2-913f-a279c4ea89b4", title: "テスト楽曲", isDisplay: true }
              ],
            }
          ],
        }
      ],
    }
  ],
  nextCursor: "eyJmaXJzdFJlbGVhc2VkT24iOiIyMDI2LTA1LTAxIiwicmVsZWFzZUdyb3VwSWQiOiIwZjIxODVjZC1iNGFiLTRmMjItYjBhMy1mZDkzY2JiZDJkZjIifQ==",
})
model ReleaseGroupListResponse {
  releaseGroups: ReleaseGroupListItem[];

  @doc("続きがある場合のみ返す")
  nextCursor?: cursor;
}
