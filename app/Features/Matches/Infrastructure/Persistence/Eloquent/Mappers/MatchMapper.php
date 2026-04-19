<?php

declare(strict_types=1);

namespace App\Features\Matches\Infrastructure\Persistence\Eloquent\Mappers;

use App\Features\Matches\Domain\Entities\MatchInvitation;
use App\Features\Matches\Domain\Entities\PadelMatch;
use App\Features\Matches\Domain\Enums\InvitationStatusEnum;
use App\Features\Matches\Domain\Enums\MatchFormatEnum;
use App\Features\Matches\Domain\Enums\MatchStatusEnum;
use App\Features\Matches\Domain\Enums\MatchTypeEnum;
use App\Features\Matches\Domain\Enums\TeamEnum;
use App\Features\Matches\Domain\ValueObjects\CourtName;
use App\Features\Matches\Domain\ValueObjects\EloChange;
use App\Features\Matches\Domain\ValueObjects\EloRating;
use App\Features\Matches\Domain\ValueObjects\InvitationStatus;
use App\Features\Matches\Domain\ValueObjects\MatchFormat;
use App\Features\Matches\Domain\ValueObjects\MatchId;
use App\Features\Matches\Domain\ValueObjects\MatchInvitationId;
use App\Features\Matches\Domain\ValueObjects\MatchStatus;
use App\Features\Matches\Domain\ValueObjects\MatchType;
use App\Features\Matches\Domain\ValueObjects\Notes;
use App\Features\Matches\Domain\ValueObjects\PlayerId;
use App\Features\Matches\Domain\ValueObjects\Score;
use App\Features\Matches\Domain\ValueObjects\SetsDetail;
use App\Features\Matches\Domain\ValueObjects\SetsToWin;
use App\Features\Matches\Domain\ValueObjects\Team;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Models\MatchInvitation as EloquentMatchInvitation;
use App\Features\Matches\Infrastructure\Persistence\Eloquent\Models\MatchModel as EloquentMatch;
use DateTimeImmutable;

final readonly class MatchMapper
{
    public function toDomain(EloquentMatch $model, array $confirmedPlayerIds = []): PadelMatch
    {
        $setsDetail = null;
        if ($model->sets_detail !== null) {
            $setsDetail = SetsDetail::fromArray($model->sets_detail);
        }

        return PadelMatch::reconstitute(
            id: MatchId::fromString($model->id),
            type: MatchType::fromEnum(MatchTypeEnum::from($model->match_type)),
            format: MatchFormat::fromEnum(MatchFormatEnum::from($model->match_format)),
            status: MatchStatus::fromEnum(MatchStatusEnum::from($model->status)),
            createdBy: PlayerId::fromString($model->created_by),
            teamAPlayer1Id: PlayerId::fromString($model->team_a_player1_id),
            teamAPlayer2Id: $model->team_a_player2_id ? PlayerId::fromString($model->team_a_player2_id) : null,
            teamBPlayer1Id: $model->team_b_player1_id ? PlayerId::fromString($model->team_b_player1_id) : null,
            teamBPlayer2Id: $model->team_b_player2_id ? PlayerId::fromString($model->team_b_player2_id) : null,
            setsDetail: $setsDetail,
            teamAScore: $model->team_a_score !== null ? Score::fromInt($model->team_a_score) : null,
            teamBScore: $model->team_b_score !== null ? Score::fromInt($model->team_b_score) : null,
            courtName: $model->court_name ? CourtName::fromString($model->court_name) : null,
            notes: $model->notes !== null ? Notes::fromString($model->notes) : null,
            teamAEloBefore: $model->team_a_elo_before !== null ? EloRating::fromInt($model->team_a_elo_before) : null,
            teamBEloBefore: $model->team_b_elo_before !== null ? EloRating::fromInt($model->team_b_elo_before) : null,
            eloChange: $model->elo_change !== null ? EloChange::fromInt($model->elo_change) : null,
            setsToWin: SetsToWin::fromInt($model->sets_to_win ?? 2),
            matchDate: $model->match_date ? new DateTimeImmutable($model->match_date) : null,
            confirmedPlayerIds: array_map(fn (string $id) => PlayerId::fromString($id), $confirmedPlayerIds),
        );
    }

    /** @return array<string, mixed> */
    public function toPersistence(PadelMatch $match): array
    {
        $setsDetail = null;
        if ($match->setsDetail() !== null) {
            $setsDetail = $match->setsDetail()->sets();
        }

        return [
            'id' => $match->id()->value(),
            'match_date' => $match->matchDate()?->format('Y-m-d H:i:s'),
            'court_name' => $match->courtName()?->value(),
            'match_type' => $match->type()->value()->value,
            'match_format' => $match->format()->value()->value,
            'status' => $match->status()->value()->value,
            'team_a_player1_id' => $match->teamAPlayer1Id()->value(),
            'team_a_player2_id' => $match->teamAPlayer2Id()?->value(),
            'team_b_player1_id' => $match->teamBPlayer1Id()?->value(),
            'team_b_player2_id' => $match->teamBPlayer2Id()?->value(),
            'sets_detail' => $setsDetail,
            'team_a_score' => $match->teamAScore()?->value(),
            'team_b_score' => $match->teamBScore()?->value(),
            'notes' => $match->notes()?->value(),
            'team_a_elo_before' => $match->teamAEloBefore()?->value(),
            'team_b_elo_before' => $match->teamBEloBefore()?->value(),
            'elo_change' => $match->eloChange()?->value(),
            'sets_to_win' => $match->setsToWin()->value(),
            'created_by' => $match->createdBy()->value(),
        ];
    }

    public function invitationToDomain(EloquentMatchInvitation $model): MatchInvitation
    {
        return MatchInvitation::reconstitute(
            id: MatchInvitationId::fromString($model->id),
            matchId: MatchId::fromString($model->match_id),
            inviteeId: PlayerId::fromString($model->invitee_id),
            team: Team::fromEnum(TeamEnum::from($model->team)),
            position: $model->position,
            status: InvitationStatus::fromEnum(InvitationStatusEnum::from($model->status)),
            invitedAt: new DateTimeImmutable($model->invited_at),
            respondedAt: $model->responded_at ? new DateTimeImmutable($model->responded_at) : null,
        );
    }

    /** @return array<string, mixed> */
    public function invitationToPersistence(MatchInvitation $invitation): array
    {
        return [
            'id' => $invitation->id()->value(),
            'match_id' => $invitation->matchId()->value(),
            'invitee_id' => $invitation->inviteeId()->value(),
            'team' => $invitation->team()->value()->value,
            'position' => $invitation->position(),
            'status' => $invitation->status()->value()->value,
            'invited_at' => $invitation->invitedAt()->format('Y-m-d H:i:s'),
            'responded_at' => $invitation->respondedAt()?->format('Y-m-d H:i:s'),
        ];
    }
}
