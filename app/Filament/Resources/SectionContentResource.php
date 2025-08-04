<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionContentResource\Pages;
use App\Filament\Resources\SectionContentResource\RelationManagers;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\SectionContent;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\TextUI\XmlConfiguration\Logging\Logging;

class SectionContentResource extends Resource
{
    protected static ?string $model = SectionContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\Select::make('course')
                        ->label('Course')
                        ->options(fn() => Course::all()->pluck('name','id'))
                        ->reactive()
                        ->searchable()
                        ->preload()
                        ->afterStateUpdated(fn(callable $set) => $set('course_section_id', null))
                        ->afterStateHydrated(function (callable $set, $state, $livewire) {
                            if ($livewire->record && $livewire->record->courseSection) {
                                $set('course', $livewire->record->courseSection->course_id);
                            }
                        })
                        ->dehydrated(false) // Jangan simpan ke database
                        ->disabled(fn ($livewire) => $livewire->record !== null) //Disable jika sedang edit
                        ->required(),

                    Forms\Components\Select::make('course_section_id')
                        ->label('Course Section')
                        ->options(function (callable $get) {
                            $courseId = $get('course');

                            if (!$courseId) return [];
                            return CourseSection::with('sectionContents.course')->where('course_id', $courseId)->pluck('name', 'id');
                        })
                        ->searchable()
                        ->disabled(fn(callable $get) => !$get('course'))
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->label('Tittle Content')
                        ->maxLength(255),

                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->columnSpanFull(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Title Content')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courseSection.course.name')
                    ->label('Course')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courseSection.name')
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
            'index' => Pages\ManageSectionContents::route('/'),
            'create'=> Pages\CreateSectionContent::route('/create'),
            'edit'  => Pages\EditSectionContent::route('/{record}/edit'),

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
