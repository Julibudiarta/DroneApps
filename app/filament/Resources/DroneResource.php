<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DroneResource\Pages;
use App\Filament\Resources\DroneResource\RelationManagers;
use App\Models\Drone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Section;
class DroneResource extends Resource
{
    protected static ?string $model = Drone::class;
    protected static ?string $navigationGroup = 'Inventory';
    protected static ?string $navigationLabel = 'Drone';

    protected static ?string $navigationIcon = 'heroicon-m-rocket-launch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Overview')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Name')
                            ->required()
                            ->maxLength(255)->columnSpan(1),
                        Forms\Components\TextInput::make('idlegal')->label('Legal ID')
                            ->required()
                            ->maxLength(255)->columnSpan(2),
                        Forms\Components\select::make('status')->label('Status')   
                            ->options([
                                'airworthy' => 'Airworthy',
                               'maintenance' => 'Maintenance',
                               'retired' => 'Retired',
                            ])
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('brand')->label('Brand')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('model')->label('Model')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('type')->label('Type')
                            ->required()
                            ->maxLength(255),
                    ])->columns(3),
                    //and wizard 1
                    Forms\Components\Wizard\Step::make('Drone Details')
                    ->schema([
                        Forms\Components\TextInput::make('inventory_id')->label('Drone Geometry')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('color')->label('Color')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('inventory_asset')->label('Inventory/Asset')
                            ->options([
                                'asset'=> 'Assets',
                                'inventory'=> 'Inventory',
                            ])
                            ->required(),
                        Forms\Components\Select::make('users_id')->label('Owner')
                            ->relationship('users','name')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('firmware_v')->label('Firmware version')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('hardware_v')->label('Hardware Version')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('propulsion_v')->label('propulsion Version')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')->label('Description')
                            ->required()
                            ->maxLength(255)->columnSpanFull(),

                    ])->columns(3),
                    //and wizard 2
                    Forms\Components\Wizard\Step::make('connect')
                    ->schema([
                        Forms\Components\TextInput::make('serial_p')->label('Serial Printed')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('serial_i')->label('Serial Internal')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('flight_c')->label('Flight controller')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('remote_c')->label('Remote Controller')
                            ->required()
                            ->maxLength(255),
                            Forms\Components\TextInput::make('remote_cc')->label('Remote Controller2')
                            ->required()
                            ->maxLength(255)->columnSpan(2),
                        Forms\Components\TextInput::make('remote')->label('Remote ID')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('conn_card')->label('Connection Card')
                            ->required()
                            ->maxLength(255)->columnSpan(2),
                    ])->columns(3),
                    //and wizard 3
                ])->columnSpanFull(),
                //end wizard
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Drone Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')
                ->color(fn ($record) => match ($record->status){
                    'airworthy' => Color::Green,
                   'maintenance' =>Color::Red,
                   'retired' => Color::Zinc
                 })
                    ->searchable(),
                Tables\Columns\TextColumn::make('idlegal')->label('Legal ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('users.name')->label('Owners')
                    ->sortable(),
                // Tables\Columns\TextColumn::make('brand')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('model')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('type')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('serial_p')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('serial_i')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('flight_c')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('remote_c')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('remote_cc')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('inventory_id')
                //     ->numeric()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('inventory_asset')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('description')
                //     ->searchable(),

                // Tables\Columns\TextColumn::make('firmware_v')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('hardware_v')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('propulsion_v')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('color')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('remote')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('conn_card')
                //     ->searchable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    //infolist
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
        
        ->schema([
            TextEntry::make('name')->label('Name'),
            TextEntry::make('idlegal')->label('Legal ID'),
            TextEntry::make('status')->label('Status'),
            TextEntry::make('users.name')->label('Owner'),

            TextEntry::make('firmware_v')->label('Firmware Version'),
            TextEntry::make('hardware_v')->label('Hardware Version'),
            TextEntry::make('serial_i')->label('Serial Internal'),
            TextEntry::make('serial_p')->label('Serial Printed'),
            TextEntry::make('remote')->label('Remote'),
            TextEntry::make('created_at')->label('Purchas Date')->date('Y-m-d'),
        ])->columns(3);
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
            'index' => Pages\ListDrones::route('/'),
            'create' => Pages\CreateDrone::route('/create'),
            'view' => Pages\ViewDrone::route('/{record}'),
            'edit' => Pages\EditDrone::route('/{record}/edit'),
        ];
    }
}
