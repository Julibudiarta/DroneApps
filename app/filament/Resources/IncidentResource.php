<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncidentResource\Pages;
use App\Filament\Resources\IncidentResource\RelationManagers;
use App\Models\drone;
use App\Models\fligh;
use App\Models\fligh_location;
use App\Models\Incident;
use App\Models\Projects;
use App\Models\User;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action;
use App\Helpers\TranslationHelper;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;
    // protected static ?string $navigationLabel = 'Incident';

    protected static ?string $navigationIcon = 'heroicon-m-exclamation-triangle';
    public static ?string $tenantOwnershipRelationshipName = 'teams';
    public static ?int $navigationSort = 7;
    public static ?string $navigationGroup = 'flight';
    protected static bool $isLazy = false;
    
    public static function getNavigationBadge(): ?string{
        $teamID = Auth()->user()->teams()->first()->id;
        return static::getModel()::Where('teams_id',$teamID)->count();
    }

    public static function getNavigationLabel(): string
    {
        return TranslationHelper::translateIfNeeded('Incident');
    }
    public static function getModelLabel(): string
    {
        return TranslationHelper::translateIfNeeded('Incident');
    }


    public static function form(Form $form): Form
    {
        $currentTeamId = auth()->user()->teams()->first()->id;
        return $form
            ->schema([
                Forms\Components\Section::make(TranslationHelper::translateIfNeeded('Incident Overview'))
                    ->description('')
                    ->schema([
                    Forms\Components\Hidden::make('teams_id')
                        ->default(auth()->user()->teams()->first()->id ?? null),
                    Forms\Components\DatePicker::make('incident_date')
                    ->label(TranslationHelper::translateIfNeeded('Incident Date'))
                    ->required(),
                    Forms\Components\Select::make('cause')
                    ->label(TranslationHelper::translateIfNeeded('Incident Cause'))    
                        ->required()
                        ->options([
                            'weather' => 'Weather',
                            'mechanic' => 'Mechanic',
                            'electronic' => 'Electronic',
                            'battery' => 'Battery',
                            'radio' => 'Radio',
                            'pilot' => 'Pilot',
                            'wildlife' => 'Wildlife',
                            'nefarious/criminal' => 'Nefarious/Criminal',
                            'human_error' => 'Human Error',
                            'others' => 'Others'
                        ]),
                    Forms\Components\Select::make('status')
                    ->label(TranslationHelper::translateIfNeeded('Status'))    
                        ->required()
                        ->options([
                            false => 'Closed',
                            true => 'Under Review',
                        ]),
                    // Forms\Components\BelongsToSelect::make('location_id')
                    Forms\Components\Select::make('location_id')
                        // ->relationship('fligh_locations', 'name')
                        ->options(function (callable $get) use ($currentTeamId) {
                            return fligh_location::where('teams_id', $currentTeamId)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->label(TranslationHelper::translateIfNeeded('Flight Locations'))
                        ->required(),
                        // ->searchable(),
                     Forms\Components\Select::make('drone_id')
                        // ->relationship('drone','name')
                        ->label(TranslationHelper::translateIfNeeded('Drone'))
                        ->options(function (callable $get) use ($currentTeamId) {
                            return drone::where('teams_id', $currentTeamId)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('project_id')
                        // ->relationship('project','case')
                        ->label(TranslationHelper::translateIfNeeded('Projects'))
                        ->options(function (callable $get) use ($currentTeamId) {
                            return Projects::where('teams_id', $currentTeamId)->pluck('case', 'id');
                        })
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('personel_involved_id')
                    ->label(TranslationHelper::translateIfNeeded('Organization Personnel Involved'))
                        ->options(
                            function (Builder $query) use ($currentTeamId) {
                                return User::whereHas('teams', function (Builder $query) use ($currentTeamId) {
                                    $query->where('team_user.team_id', $currentTeamId); 
                            })->pluck('name','id');
                        }  
                        )->searchable()
                        ->columnSpanFull(),
                    ])->columns(2),
                    //section 2
                Forms\Components\Section::make(TranslationHelper::translateIfNeeded('Incident Description'))
                    ->description('')
                    ->schema([
                        Forms\Components\TextArea::make('aircraft_damage')
                        ->label(TranslationHelper::translateIfNeeded('Aircraft Damage'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextArea::make('other_damage')
                    ->label(TranslationHelper::translateIfNeeded('Other Damage'))
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextArea::make('description')
                    ->label(TranslationHelper::translateIfNeeded('Description'))
                        ->required()
                        ->maxLength(255)->columnSpanFull(),
                    Forms\Components\TextInput::make('incuration_type')
                    ->label(TranslationHelper::translateIfNeeded('Incursions (people, aircraft...)'))
                        ->required()
                        ->maxLength(255)->columnSpanFull(),
                    ])->columns(2),
                    //section 3
                Forms\Components\Section::make(TranslationHelper::translateIfNeeded('Incident Rectification'))
                ->description('')
                ->schema([
                    Forms\Components\TextInput::make('rectification_note')
                    ->label(TranslationHelper::translateIfNeeded('Rectification Notes'))
                        ->required()
                        ->maxLength(255)->columnSpanFull(),
                    Forms\Components\DatePicker::make('rectification_date')
                    ->label(TranslationHelper::translateIfNeeded('Rectification Date'))
                        ->required(),
                    Forms\Components\TextInput::make('Technician')
                    ->label(TranslationHelper::translateIfNeeded('Technician'))
                        ->required()
                        ->maxLength(255),
                ])->columns(2),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('incident_date')
                ->label(TranslationHelper::translateIfNeeded('Incident Date'))    
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cause')
                ->label(TranslationHelper::translateIfNeeded('Cause'))    
                    ->searchable(),
                Tables\Columns\TextColumn::make('aircraft_damage')
                ->label(TranslationHelper::translateIfNeeded('Aircraft Damage'))    
                    ->searchable(),

                // Tables\Columns\TextColumn::make('other_damage')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('description')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('incuration_type')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('rectification_note')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('rectification_date')
                //     ->date()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('Technician')
                    ->label(TranslationHelper::translateIfNeeded('Technician'))
                    ->searchable(),

                // Tables\Columns\TextColumn::make('location_id')
                //     ->numeric()
                //     ->sortable(),

                Tables\Columns\TextColumn::make('drone.name')
                ->label(TranslationHelper::translateIfNeeded('Drones'))    
                    ->numeric()
                    ->url(fn($record) => $record->drone_id?route('filament.admin.resources.drones.view', [
                        'tenant' => Auth()->user()->teams()->first()->id,
                        'record' => $record->drone_id,
                    ]):null)->color(Color::Blue)
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.case')
                ->label(TranslationHelper::translateIfNeeded('Projects'))    
                    ->numeric()
                    ->url(fn($record) => $record->project_id?route('filament.admin.resources.projects.index', [
                        'tenant' => Auth()->user()->teams()->first()->id,
                        'record' => $record->project_id,
                    ]):null)->color(Color::Blue)
                    ->sortable(),
                // Tables\Columns\TextColumn::make('personel_involved_id')
                //     ->numeric()
                //     ->sortable(),
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
                    false => 'Closed',
                    true => 'Under Review',
                ])
                ->label(TranslationHelper::translateIfNeeded('Status')),
                Tables\Filters\SelectFilter::make('create_at') 
                ->label(TranslationHelper::translateIfNeeded('Create at'))
            ])
            ->actions([
                // Action::make('viewFlight')
                // ->label('View Flight')
                // ->url(function ($record) {
                //     $flight = fligh::where('projects_id', $record->project_id)
                //         ->where('location_id', $record->location_id)
                //         ->where('drones_id', $record->drone_id)
                //         ->orderBy('start_date_flight', 'desc')
                //         ->first();

                //     if (!$flight) {
                //             $flight = fligh::where('drones_id', $record->drone_id)
                //                 ->orderBy('start_date_flight', 'desc')
                //                 ->first();
                //         }

                //     return $flight
                //         ? route('filament.admin.resources.flighs.view', [
                //             'tenant' => auth()->user()->teams()->first()->id,
                //             'record' => $flight->id,
                //         ])
                //         : null; 
                // })
                // ->button()
                // ->requiresConfirmation(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
  
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        ->schema([
            Section::make(TranslationHelper::translateIfNeeded('Incident Overview'))
                ->schema([
                    TextEntry::make('incident_date')->label(TranslationHelper::translateIfNeeded('Incident Date')),
                    TextEntry::make('cause')->label(TranslationHelper::translateIfNeeded('Cause')),
                    TextEntry::make('status')->label(TranslationHelper::translateIfNeeded('Status')),
                    TextEntry::make('location_id')->label(TranslationHelper::translateIfNeeded('Locations')),
                    TextEntry::make('drone.name')
                        ->label(TranslationHelper::translateIfNeeded('Drones'))
                        ->url(fn($record) => $record->drone_id ? route('filament.admin.resources.drones.view', [
                            'tenant' => Auth()->user()->teams()->first()->id,
                            'record' => $record->drone_id,
                        ]) : null)->color(Color::Blue),
                    TextEntry::make('project.case')
                        ->label(TranslationHelper::translateIfNeeded('Projects'))
                        ->url(fn($record) => $record->project_id ? route('filament.admin.resources.projects.index', [
                            'tenant' => Auth()->user()->teams()->first()->id,
                            'record' => $record->project_id,
                        ]) : null)->color(Color::Blue),
                    TextEntry::make('personel_involved_id')->label(TranslationHelper::translateIfNeeded('Organization Personnel Involved')),
                ])->columns(4),
            Section::make(TranslationHelper::translateIfNeeded('Incident Description'))
                ->schema([
                    TextEntry::make('aircraft_damage')->label(TranslationHelper::translateIfNeeded('Aircraft Damage')),
                    TextEntry::make('other_damage')->label(TranslationHelper::translateIfNeeded('Other Damage')),
                    TextEntry::make('description')->label(TranslationHelper::translateIfNeeded('Description')),
                    TextEntry::make('incuration_type')->label(TranslationHelper::translateIfNeeded('Incuration Type')),
                ])->columns(4),
            Section::make(TranslationHelper::translateIfNeeded('Incident Rectification'))
                ->schema([
                    TextEntry::make('rectification_note')->label(TranslationHelper::translateIfNeeded('Rectification Note')),
                    TextEntry::make('rectification_date')->label(TranslationHelper::translateIfNeeded('Rectification Date')),
                    TextEntry::make('Technician')->label(TranslationHelper::translateIfNeeded('Technician')),
                ])->columns(3)
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
            'index' => Pages\ListIncidents::route('/'),
            'create' => Pages\CreateIncident::route('/create'),

            //'view' => Pages\ViewIncident::route('/{record}'),

            'edit' => Pages\EditIncident::route('/{record}/edit'),
        ];
    }
}
