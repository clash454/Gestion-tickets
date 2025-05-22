<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Gestion des utilisateurs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('telephone')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'admin' => 'Administrateur',
                        'utilisateur' => 'Utilisateur',
                        'technicien_interne' => 'Technicien interne',
                        'prestataire' => 'Prestataire',
                    ])
                    ->required(),
                Forms\Components\Select::make('prestataire_id')
                    ->label('Prestataire')
                    ->options(function () {
                        return User::where('type', 'prestataire')->pluck('name', 'id');
                    })
                    ->visible(fn (Forms\Get $get) => $get('type') !== 'prestataire')
                    ->searchable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telephone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'technicien_interne' => 'warning',
                        'prestataire' => 'success',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('prestataire.name')
                    ->label('Prestataire')
                    ->visible(fn ($record) => $record->prestataire_id !== null),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'admin' => 'Administrateur',
                        'utilisateur' => 'Utilisateur',
                        'technicien_interne' => 'Technicien interne',
                        'prestataire' => 'Prestataire',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
} 