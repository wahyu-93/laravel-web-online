<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class CourseService
{
    // user gabung kelas
    public function enrollUser(Course $course)
    {
        $user = Auth::user();

        // cek kelas berdasarka user id
        if(!$course->courseStudents()->where('user_id', $user)->exists()){
            // jika tidak ada maka simpan data user ke kelas
            $course->courseStudents()->create([
                'user_id'   => $user->id,
                'is_active' => true,
            ]);
        };

        return $user->name;
    }

    // mengambil id section pertama dan id content pertama dari section
    public function getFirstSectionAndContent(Course $course)
    {
        $firstSectionId = $course->courseSections()->orderBy('id')->value('id'); //maksud dari value ambil idnya saja tapi id yg pertama, 

        // lakukan pengecekan jika firstSectionId ada nilainya.
        // kemudian cari sectionContent Pertama
        $firstContentId = $firstSectionId ? $course->courseSections()->find($firstSectionId)->sectionContents()->orderBy('id')->value('id') : null;

        return [
            'firstSectionId' => $firstSectionId,
            'firstContentId' => $firstContentId,
        ];
    }

    // get Data content
    public function getLearningData(Course $course, $contentSectionId, $sectionContentId)
    {
        $course->load(['courseSections.sectionContents']);

        // cek posisi section dan content aktif user saat ini
        $currentSection = $course->courseSections()->find($contentSectionId);
        $currentContent = $currentSection ? $currentSection->sectionContents->find($currentSection) : null;

        $nextContent = null;

        // jika currentContent ada nilanya
        if($currentContent){
            $nextContent = $currentSection->courseSections
                ->where('id', '>', $currentContent->id)
                ->sortBy('id')
                ->first();
        };

        // jika currentContent nilainya null tapi currentSectionnya ada
        // maka harus pindah ke section selanjutnya, kemungkinan content disection sebelumnya sudah habis
        if(!$nextContent && $currentSection){
            // get data sectionnya
            $nextSection = $course->courseSections
                ->where('id', '>', $currentSection->id)
                ->sortBy('id')
                ->first();

            if($nextSection){
                // ambil data yg pertama kemudian masukkan data section kedalam nextcontent
                $nextContent = $nextSection->sectionContents->sortBy('id')->first();
            };
        };

        return [
            'course'         => $course,
            'currentSection' => $currentSection,
            'currentContent' => $currentContent,
            'nextContent'    => $nextContent,
            'isFinished'     => !$nextContent,
        ];
    }

}