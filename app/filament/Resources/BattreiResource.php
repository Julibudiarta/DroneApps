<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BattreiResource\Pages;
use App\Filament\Resources\BattreiResource\RelationManagers;
use App\Models\Battrei;
use App\Models\drone;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Carbon\Carbon;
use App\Helpers\TranslationHelper;


class BattreiResource extends Resource
{
    protected static ?string $model = Battrei::class;
    // protected static ?string $navigationGroup = 'Inventory';
    // protected static ?string $navigationLabel = 'Batteries';
    // protected static ?string $modelLabel = 'Batteries';
    protected static ?string $navigationIcon = 'heroicon-s-battery-100';
    public static ?string $tenantOwnershipRelationshipName = 'teams';
    protected static bool $isLazy = false;
    
    public static function getNavigationBadge(): ?string{
        $teamID = Auth()->user()->teams()->first()->id;
        return static::getModel()::Where('teams_id',$teamID)->count();
    }

    public static function getNavigationLabel(): string
    {
        return TranslationHelper::translateIfNeeded('Batteries');
    }
    public static function getModelLabel(): string
    {
        return TranslationHelper::translateIfNeeded('Batteries');
    }

    public static function form(Form $form): Form
    {
        $currentTeamId = auth()->user()->teams()->first()->id;
        return $form
            ->schema([
                Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make(TranslationHelper::translateIfNeeded('Overview'))
                    ->schema([
                        Forms\Components\Hidden::make('teams_id')
                            ->default(auth()->user()->teams()->first()->id ?? null),
                        Forms\Components\TextInput::make('name')
                            ->label(TranslationHelper::translateIfNeeded('Name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')
                            ->label(TranslationHelper::translateIfNeeded('Model'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label(TranslationHelper::translateIfNeeded('Status'))
                            ->options([
                                'airworthy' => 'Airworthy',
                                'maintenance' => 'Maintenance',
                                'retired' => 'Retired'
                            ])
                            ->required(),
                        Forms\Components\Select::make('asset_inventory')
                            ->label(TranslationHelper::translateIfNeeded('Inventory/Asset'))
                            ->options([
                                'asset' => 'Asset',
                                'inventory' => 'Inventory',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('serial_P')
                            ->label(TranslationHelper::translateIfNeeded('Serial #(Printed)'))
                            ->required()->columnSpan(2),
                        Forms\Components\TextInput::make('serial_I')
                            ->label(TranslationHelper::translateIfNeeded('Serial #(Internal)'))
                            ->required()->columnSpan(2),
                        Forms\Components\BelongsToSelect::make('for_drone')
                            ->label(TranslationHelper::translateIfNeeded('For Drone (Optional)'))
                            ->searchable()
                            ->options(function (callable $get) use ($currentTeamId) {
                                return drone::where('teams_id', $currentTeamId)->pluck('name', 'id');
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cellCount')
                            ->label(TranslationHelper::translateIfNeeded('Cell Count'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('nominal_voltage')
                            ->label(TranslationHelper::translateIfNeeded('Nominal Voltage (V)'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('capacity')
                            ->label(TranslationHelper::translateIfNeeded('Capacity (mAh)'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('initial_Cycle_count')
                            ->label(TranslationHelper::translateIfNeeded('Initial Cycle Count'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('life_span')
                            ->label(TranslationHelper::translateIfNeeded('Life Span'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('flaight_count')
                            ->label(TranslationHelper::translateIfNeeded('Flight Count'))
                            ->required()
                            ->numeric()->columnSpan(1),
                        ])->columns(4),
                        //end wizard 1
                    Tabs\Tab::make(TranslationHelper::translateIfNeeded('Extra Information'))
                    ->schema([
                        Forms\Components\Select::make('users_id')
                            ->label(TranslationHelper::translateIfNeeded('Owner'))
                            //->relationship('users', 'name')
                            ->options(function () {
                                $currentTeamId = auth()->user()->teams()->first()->id; 
                        
                                return User::whereHas('teams', function (Builder $query) use ($currentTeamId) {
                                    $query->where('team_user.team_id', $currentTeamId); 
                                })->pluck('name', 'id'); 
                            }) 
                            ->required(),
                        Forms\Components\DatePicker::make('purchase_date')
                            ->label(TranslationHelper::translateIfNeeded('Purchase Date'))
                            ->required(),
                        Forms\Components\TextInput::make('insurable_value')
                            ->label(TranslationHelper::translateIfNeeded('Insurable Value'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('wight')
                            ->label(TranslationHelper::translateIfNeeded('Weight'))
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('firmware_version')
                            ->label(TranslationHelper::translateIfNeeded('Firmware Version'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hardware_version')
                            ->label(TranslationHelper::translateIfNeeded('Hardware Version'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_loaner')
                            ->label(TranslationHelper::translateIfNeeded('Loaner Battery'))
                            ->required(),
                        Forms\Components\TextInput::make('description')
                            ->label(TranslationHelper::translateIfNeeded('Description'))
                            ->required()
                            ->maxLength(255)->columnSpanFull(),
                        ])->columns(3),
                    ])->columnSpanFull(),
            ]);
    }

    

    public static function table(Table $table): Table
    {
        return $table
            //edit query untuk action shared un-shared
            ->modifyQueryUsing(function (Builder $query) {
                $userId = auth()->user()->id;

                if (Auth()->user()->roles()->pluck('name')->contains('super_admin') || (Auth()->user()->roles()->pluck('name')->contains('panel_user'))) {
                    return $query;
                }else{
                    $query->where(function ($query) use ($userId) {
                        $query->where('users_id', $userId);
                    })
                    ->orWhere(function ($query) use ($userId) {
                        $query->where('users_id', '!=', $userId)->where('shared', 1);
                    });
                    return $query;
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(TranslationHelper::translateIfNeeded('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('model')
                    ->label(TranslationHelper::translateIfNeeded('Model'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(TranslationHelper::translateIfNeeded('Status'))
                    // ->color(fn ($record) => match ($record->status){
                    //     'airworthy' => Color::Green,
                    //    'maintenance' =>Color::Red,
                    //    'retired' => Color::Zinc
                    //  })
                    ->formatStateUsing(function ($state) {
                        $colors = [
                            'airworthy' => '#28a745',
                            'maintenance' => 'red',
                            'retired' => 'gray',
                        ];
                
                        $color = $colors[$state] ?? 'gray';
                
                        return "<span style='
                                display: inline-block;
                                width: 10px;
                                height: 10px;
                                background-color: {$color};
                                border-radius: 50%;
                                margin-right: 5px;
                            '></span><span style='color: {$color};'>{$state}</span>";
                    })
                    ->html()
                    ->searchable(),
                Tables\Columns\TextColumn::make('flight_time')
                    ->label(TranslationHelper::translateIfNeeded('Flights & Flying Time'))
                    ->getStateUsing(function ($record) {
                       $flights = $record->fligh()
                           ->whereHas('teams', function ($query) {
                               $query->where('teams.id', auth()->user()->teams()->first()->id);
                           })
                           ->get()
                           ->merge(
                               $record->kits()->with(['fligh' => function ($query) {
                                   $query->whereHas('teams', function ($query) {
                                       $query->where('teams.id', auth()->user()->teams()->first()->id);
                                   });
                               }])->get()->pluck('fligh')->flatten()
                           );
                   
                       $totalSeconds = 0;
                       foreach ($flights as $flight) {
                           $start = $flight->start_date_flight;
                           $end = $flight->end_date_flight;
                           if ($start && $end) {
                               $totalSeconds += Carbon::parse($start)->diffInSeconds(Carbon::parse($end));
                           }
                       }
                   
                       $hours = floor($totalSeconds / 3600);
                       $minutes = floor(($totalSeconds % 3600) / 60);
                       $seconds = $totalSeconds % 60;
                       $totalDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                       $totalFlights = $flights->unique('id')->count();
                    return "<div> ({$totalFlights}) " . TranslationHelper::translateIfNeeded('Flights') . " <div class='inline-block border border-gray-300 dark:border-gray-600 px-2 py-1 rounded bg-gray-200 dark:bg-gray-700'>
                            <strong class='text-gray-800 dark:text-gray-200'>{$totalDuration}</strong></div>";
                   })
                    ->sortable()
                    ->html(),
                // Tables\Columns\TextColumn::make('asset_inventory')->label('Inventory/Asset')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('serial_P')->label('Serial Printed')
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('serial_I')->label('Serial Internal')
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('cellCount')->label('Cell Count')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('nominal_voltage')->label('Voltage')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('capacity')->label('Capacity')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('initial_Cycle_count')->label('Initial Cycles Count')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('life_span')
                    ->label(TranslationHelper::translateIfNeeded('Life Span'))
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('flaight_count')->label('Flaight Count')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('drone.name')
                    ->label(TranslationHelper::translateIfNeeded('For Drone'))
                    ->numeric()->url(fn($record) => $record->for_drone ? route('filament.admin.resources.drones.view', [
                        'tenant' => Auth()->user()->teams()->first()->id,
                        'record' => $record->for_drone,
                    ]): null)->color(Color::Blue)
                    ->sortable(),
                // Tables\Columns\TextColumn::make('purchase_date')->label('Purchase Date')
                //     ->date()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('insurable_value')->label('Insurable Value')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('wight')->label('weight')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('firmware_version')->label('Firmware Version')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('hardware_version')->label('Hardware Version')
                //     ->searchable(),
                // Tables\Columns\IconColumn::make('is_loaner')->label('Is Loaner')
                //     ->boolean(),
                // Tables\Columns\TextColumn::make('description')->label('Description')
                //     ->searchable(),
                Tables\Columns\TextColumn::make('users.name')
                    ->label(TranslationHelper::translateIfNeeded('Owners'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(TranslationHelper::translateIfNeeded('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(TranslationHelper::translateIfNeeded('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                ->options([
                    'airworthy' => 'Airworthy',
                   'maintenance' => 'Maintenance',
                   'retired' => 'Retired'
                ])
                ->label(TranslationHelper::translateIfNeeded('Filter by Status')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('showBattrey')
                    ->url(fn ($record) => route('battery.statistik', ['battery_id' => $record->id]))->label(TranslationHelper::translateIfNeeded('View'))
                    ->icon('heroicon-s-eye'),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    //Shared action
                    Tables\Actions\Action::make('Shared')->label(TranslationHelper::translateIfNeeded('Shared'))
                    ->hidden(fn ($record) => 
                    ($record->shared == 1) ||
                    !(Auth()->user()->roles()->pluck('name')->contains('super_admin') || (Auth()->user()->roles()->pluck('name')->contains('panel_user'))) && 
                    ($record->users_id != Auth()->user()->id))
    
                    ->action(function ($record) {
                        $record->update(['shared' => 1]);
                        Notification::make()
                        ->title(TranslationHelper::translateIfNeeded('Shared Updated'))
                        ->body(TranslationHelper::translateIfNeeded("Shared successfully changed."))
                        ->success()
                        ->send();
                    })->icon('heroicon-m-share'),
                    //Un-Shared action
                    Tables\Actions\Action::make('Un-Shared')->label(TranslationHelper::translateIfNeeded('Un-Shared'))
                        ->hidden(fn ($record) => 
                        ($record->shared == 0) ||
                        !(Auth()->user()->roles()->pluck('name')->contains('super_admin') || (Auth()->user()->roles()->pluck('name')->contains('panel_user')))&&
                        ($record->users_id != Auth()->user()->id))
                        ->action(function ($record) {
                            $record->update(['shared' => 0]);
                            Notification::make()
                            ->title(TranslationHelper::translateIfNeeded('Un-Shared Updated '))
                            ->body(TranslationHelper::translateIfNeeded("Un-Shared successfully changed."))
                            ->success()
                            ->send();
                        })->icon('heroicon-m-share'),
                ])
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
//infolist battery
public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
    
    ->schema([
        Section::make(TranslationHelper::translateIfNeeded('Overview'))
    ->schema([
        TextEntry::make('name')->label(TranslationHelper::translateIfNeeded('Name')),
        TextEntry::make('model')->label(TranslationHelper::translateIfNeeded('Model')),
        TextEntry::make('status')->label(TranslationHelper::translateIfNeeded('Status'))
            ->color(fn ($record) => match ($record->status) {
                'airworthy' => Color::Green,
                'maintenance' => Color::Red,
                'retired' => Color::Zinc,
            }),
        TextEntry::make('asset_inventory')->label(TranslationHelper::translateIfNeeded('Asset Inventory')),
        TextEntry::make('serial_P')->label(TranslationHelper::translateIfNeeded('Serial Printed')),
        TextEntry::make('serial_I')->label(TranslationHelper::translateIfNeeded('Serial Internal')),
        TextEntry::make('cellCount')->label(TranslationHelper::translateIfNeeded('Cell Count')),
        TextEntry::make('nominal_voltage')->label(TranslationHelper::translateIfNeeded('Voltage')),
        TextEntry::make('capacity')->label(TranslationHelper::translateIfNeeded('Capacity')),
        TextEntry::make('initial_Cycle_count')->label(TranslationHelper::translateIfNeeded('Initial Cycles Count')),
        TextEntry::make('life_span')->label(TranslationHelper::translateIfNeeded('Life Span')),
        TextEntry::make('flaight_count')->label(TranslationHelper::translateIfNeeded('Flight Count')),
        TextEntry::make('drone.name')->label(TranslationHelper::translateIfNeeded('For Drone (Optional)'))
            ->url(fn($record) => $record->for_drone ? route('filament.admin.resources.drones.view', [
                'tenant' => auth()->user()->teams()->first()->id,
                'record' => $record->for_drone,
            ]) : null)
            ->color(Color::Blue),
    ])->columns(5),

Section::make(TranslationHelper::translateIfNeeded('Extra Information'))
    ->schema([
        TextEntry::make('users.name')->label(TranslationHelper::translateIfNeeded('Owner'))
            ->url(fn($record) => $record->for_drone ? route('filament.admin.resources.users.index', [
                'tenant' => auth()->user()->teams()->first()->id,
                'record' => $record->users_id,
            ]) : null)
            ->color(Color::Blue),
        TextEntry::make('purchase_date')->label(TranslationHelper::translateIfNeeded('Purchase Date')),
        TextEntry::make('insurable_value')->label(TranslationHelper::translateIfNeeded('Insurable Value')),
        TextEntry::make('wight')->label(TranslationHelper::translateIfNeeded('Weight')),
        TextEntry::make('firmware_version')->label(TranslationHelper::translateIfNeeded('Firmware Version')),
        TextEntry::make('hardware_version')->label(TranslationHelper::translateIfNeeded('Hardware Version')),
        IconEntry::make('is_loaner')->boolean()->label(TranslationHelper::translateIfNeeded('Loaner Battery')),
        TextEntry::make('description')->label(TranslationHelper::translateIfNeeded('Description')),
    ])->columns(4)
                ]);
}
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBattreis::route('/'),
            'create' => Pages\CreateBattrei::route('/create'),
            'view' => Pages\ViewBattrei::route('/{record}'),
            'edit' => Pages\EditBattrei::route('/{record}/edit'),
        ];
    }
}
