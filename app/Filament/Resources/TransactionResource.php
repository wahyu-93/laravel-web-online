<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Pricing;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make([
                    Step::make('Product and Price')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Forms\Components\Select::make('pricing_id')
                                        ->relationship('pricing', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function($state, callable $set){
                                            $pricing = Pricing::find($state);

                                            if (!$pricing) {
                                                $set('total_tax_amount', null);
                                                $set('grand_total_amount', null);
                                                $set('sub_total_amount', null);
                                                $set('duration', null);
                                                return;
                                            };
                                            
                                            $price = $pricing->price;
                                            $duration = $pricing->duration;

                                            $subTotal = $price * $state;
                                            // $subTotal = $price;
                                            $totalPpn = $subTotal * 0.11;
                                            $totalAmount = $subTotal + $totalPpn;

                                            $set('total_tax_amount', $totalPpn);
                                            $set('grand_total_amount', $totalAmount);
                                            $set('sub_total_amount', $price);
                                            $set('duration', $duration);
                                        })
                                        ->afterStateHydrated(function(callable $set, $state){
                                            $pricingId = $state;
                                            if($pricingId){
                                                $pricing = Pricing::find($pricingId);
                                                $duration = $pricing->duration;
                                                $set('duration', $duration);
                                            }
                                        }),

                                    Forms\Components\TextInput::make('duration')
                                        ->prefix('month')
                                        ->readOnly(),
                                ]),

                            Grid::make(3)
                                ->schema([
                                    Forms\Components\TextInput::make('sub_total_amount')
                                        ->prefix('IDR')
                                        ->readonly(),

                                    Forms\Components\TextInput::make('grand_total_amount')
                                        ->prefix('IDR')
                                        ->readonly(),

                                    Forms\Components\TextInput::make('total_tax_amount')
                                        ->prefix('IDR')
                                        ->readonly()
                                        ->helperText('Sudah Termasuk Pajak 11%'),
                                ]),
                            
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('started_at')
                                        ->live()
                                        ->afterStateUpdated(function($state, callable $set, callable $get){
                                            $duration = $get('duration');
                                            if($state && $duration){
                                                $endedAt = \Carbon\Carbon::parse($state)->addMonth($duration);
                                                $set('ended_at', $endedAt->format('Y-m-d'));
                                            }
                                        })
                                        ->required(),

                                    DatePicker::make('ended_at')
                                        ->readonly(),
                                ])
                        ])
                ])
                ->columns(1)
                ->columnSpan('full')
                ->skippable(),

               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_trx_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pricing_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tax_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')
                    ->boolean(),
                Tables\Columns\TextColumn::make('payment_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('proof')
                    ->searchable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ended_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
            'edit'  => Pages\EditTransaction::route('/{record}/edit'),
            'create'  => Pages\CreateTransaction::route('/create'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
