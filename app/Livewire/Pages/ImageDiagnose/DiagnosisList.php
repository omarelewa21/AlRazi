<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Models\Diagnose;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;

class DiagnosisList extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                TextColumn::make('created_at')->label('Date & Time')->dateTime('Y-m-d H:i A')->sortable(),
                TextColumn::make('id')->label('Worklist Id')->sortable(),
                TextColumn::make('patient.name')->label('Patient Name')->sortable()->searchable(),
                TextColumn::make('patient.age')->label('Patient Age')->sortable()->searchable(),
                TextColumn::make('DiganoseUser.referredBy.name')->label('Referral')->searchable()->sortable(),
                TextColumn::make('DiganoseUser.priority')->label('Priority')->sortable()
                    ->default('low')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'gray',
                    }),
                TextColumn::make('status')->label('Status')->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'primary',
                        'Approved' => 'success',
                    }),
            ])
            ->defaultSort('DiganoseUser.priority')
            ->filters([
                // ...
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('Review')
                        ->url(fn (Diagnose $record): string => route('diagnose.show', $record))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    Action::make('Share')
                        ->action(function (Diagnose $record) {
                            $this->dispatch('openModal', component: 'pages.image-diagnose.share', arguments: ['diagnose' => $record->id]);
                        })
                        ->icon('heroicon-o-share')
                        ->color('warning'),
                ])
                ->color('info')
                ->tooltip('Actions'),

            ])
            ->bulkActions([
                // ...
            ]);
    }

    protected function getQuery()
    {
        return Diagnose::where(function($query) {
                $query->whereRelation('patient', 'user_id', auth()->id())
                    ->orWhereRelation('users', 'user_id', auth()->id());
            })
            ->select('id', 'patient_id', 'created_at', 'status');
    }
}
