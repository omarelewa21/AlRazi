<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Mail\DiagnoseShared;
use LivewireUI\Modal\ModalComponent;
use App\Models\Diagnose;
use App\Models\DiagnoseUser;
use App\Models\User;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Mail;

class Share extends ModalComponent implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Diagnose $diagnose;
    public $priorities = [];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Users list')
            ->query(User::where('id', '!=', auth()->id()))
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('email')->sortable()->searchable(),
                ViewColumn::make('priority')->view('filament.tables.columns.priority')
            ])
            ->filters([
                // ...
            ])
            ->actions([
                Action::make('Share')
                    ->action(function (User $record) {
                        $this->shareDiagnose($record);
                    })
                    ->color('warning')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.pages.image-diagnose.share');
    }

    public function priorityChanged($userId, $value)
    {
        $this->priorities[$userId] = $value;
    }

    protected function shareDiagnose(User $user)
    {
        if($user->diagnoseUsers()->where('diagnose_id', $this->diagnose->id)->exists()) {
            return;
        }

        $diagnoseUser = DiagnoseUser::create([
            'diagnose_id' => $this->diagnose->id,
            'user_id' => $user->id,
            'priority' => isset($this->priorities[$user->id]) ? $this->priorities[$user->id] : 'low'
        ]);

        $this->dispatch('notify-user', email: $user->email, diagnoseUser: $diagnoseUser->id);
    }

    #[On('notify-user')]
    public function notifyUser(string $email, DiagnoseUser $diagnoseUser)
    {
        Mail::to($email)->send(new DiagnoseShared($diagnoseUser));
    }
}
