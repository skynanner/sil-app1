<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Role;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $modelLabel = 'Peran / Role';
    protected static ?string $pluralModelLabel = 'Peran / Role';
    protected static string|UnitEnum|null $navigationGroup = '🔧 Master Data';
    protected static ?int $navigationSort = 8;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Role')
                    ->disabled(),
                TextInput::make('name')
                    ->label('Nama Role')
                    ->disabled(),
                TextInput::make('description')
                    ->label('Deskripsi')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Role')
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->wrap(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Jumlah Pengguna')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
        ];
    }
}
