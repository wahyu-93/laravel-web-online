<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Pricing;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';

    protected static ?string $navigationGroup = 'Customers';

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
                        ]),

                    Step::make('Customer Information')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->label('Student')
                                ->relationship('user', 'email')
                                ->preload()
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function($state, callable $set){
                                    $user = User::find($state);

                                    if(!$user){
                                        return;
                                    };

                                    $name = $user->name;
                                    $email = $user->email;

                                    $set('name', $name);
                                    $set('email', $email);
                                })
                                ->afterStateHydrated(function(callable $set, $state){
                                    $userId = $state;

                                    if($userId){
                                        $user = User::find($userId);
                                        
                                        $name = $user->name;
                                        $email = $user->email;

                                        $set('name', $name);
                                        $set('email', $email);
                                    }
                                }),
                            
                            Forms\Components\TextInput::make('name')
                                ->readonly(),

                            Forms\Components\TextInput::make('email')
                                ->readonly(),
                        ]),
                    
                    Step::make('Payment Information')
                        ->schema([
                            Forms\Components\ToggleButtons::make('is_paid')
                                ->label('Apakah Sudah Membayar')
                                ->boolean()
                                ->grouped()
                                ->icons([
                                    true    => 'heroicon-o-pencil',
                                    false   => 'heroicon-o-clock',
                                ])
                                ->required(),

                            Forms\Components\Select::make('payment_type')
                                ->options([
                                    'Midtrans'  => 'Midtrans',
                                    'Manual'    => 'Manual'
                                ])
                                ->required(),

                            Forms\Components\FileUpload::make('proof')
                                ->directory('proof')
                                ->image(),

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
                
                Tables\Columns\ImageColumn::make('user.photo')
                    ->label('Photo')
                    ->circular(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pricing.name')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total_amount')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_paid')
                    ->label('Terverifikasi')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('started_at')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('ended_at')
                    ->date()
                    ->sortable(),
                
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function($record){
                        $record->is_paid = true;
                        $record->save();

                        Notification::make()
                            ->title('Order Approved')
                            ->success()
                            ->body('The Orde has been Successfully Approved.')
                            ->send();
                    })
                    ->visible(fn($record) => !$record->is_paid),
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
