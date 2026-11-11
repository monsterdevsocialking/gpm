<?php

namespace App\Enums;

enum QuoteRequestStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case SupplierResearch = 'supplier_research';
    case ProposalPreparation = 'proposal_preparation';
    case ProposalSent = 'proposal_sent';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Recebido',
            self::UnderReview => 'Em análise',
            self::SupplierResearch => 'Consulta a fornecedores',
            self::ProposalPreparation => 'Preparação da proposta',
            self::ProposalSent => 'Proposta enviada',
            self::Closed => 'Concluído',
            self::Cancelled => 'Cancelado',
        };
    }
}
