import "../../shared";
import "../../packages/media";
import "../../packages/song";
import "../people/domain.tsp";

using IsekaiObservatory.Shared;
using IsekaiObservatory.Packages.Media;
using IsekaiObservatory.Packages.Song;

namespace IsekaiObservatory.Public;

@doc("クレジットの役割")
@enumExtensions
enum SongCreditRoleValue {
  @doc("作詞")
  Lyricist: 1,

  @doc("作曲")
  Composer: 2,

  @doc("編曲")
  Arranger: 3,
}

@doc("人物が楽曲に関わった役割。同じ人物が複数の役割で載ることがある")
model SongCredit {
  personId: personId;
  role: SongCreditRoleValue;
}

@doc("公開楽曲")
model Song {
  songId: songId;
  title: title;
  description: description;
  type: SongTypeValue;

  @doc("クレジット (表示順)")
  credits: SongCredit[];

  @doc("関連する公開メディアの ID (表示順)")
  mediaIds: mediaId[];
}
