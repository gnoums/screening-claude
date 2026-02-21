<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * CRUD de pruebas de tamizaje, preguntas, opciones y reglas.
 */
class AssessmentController extends Controller
{
    // ----------------------------------------------------------------
    // Assessments
    // ----------------------------------------------------------------

    public function index(): View
    {
        $assessments = Assessment::withCount('questions')->latest()->get();

        return view('admin.assessments.index', compact('assessments'));
    }

    public function create(): View
    {
        return view('admin.assessments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slug'              => ['required', 'string', 'max:80', 'unique:assessments,slug', 'alpha_dash'],
            'name'              => ['required', 'string', 'max:150'],
            'short_name'        => ['nullable', 'string', 'max:30'],
            'description'       => ['nullable', 'string'],
            'version'           => ['nullable', 'string', 'max:20'],
            'author'            => ['nullable', 'string', 'max:150'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'credits_cost'      => ['required', 'integer', 'min:1'],
            'is_active'         => ['boolean'],
        ]);

        $assessment = Assessment::create($validated);

        return redirect()
            ->route('admin.assessments.show', $assessment)
            ->with('success', 'Prueba creada. Ahora añade las preguntas y reglas.');
    }

    public function show(Assessment $assessment): View
    {
        $assessment->load(['questions.options', 'rules']);

        return view('admin.assessments.show', compact('assessment'));
    }

    public function edit(Assessment $assessment): View
    {
        return view('admin.assessments.edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:150'],
            'short_name'        => ['nullable', 'string', 'max:30'],
            'description'       => ['nullable', 'string'],
            'version'           => ['nullable', 'string', 'max:20'],
            'author'            => ['nullable', 'string', 'max:150'],
            'estimated_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'credits_cost'      => ['required', 'integer', 'min:1'],
            'is_active'         => ['boolean'],
        ]);

        $assessment->update($validated);

        return back()->with('success', 'Prueba actualizada.');
    }

    // ----------------------------------------------------------------
    // Questions
    // ----------------------------------------------------------------

    public function storeQuestion(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_code' => ['nullable', 'string', 'max:30'],
            'is_required'   => ['boolean'],
        ]);

        $order = $assessment->questions()->max('order') + 1;
        $assessment->questions()->create(array_merge($validated, ['order' => $order]));

        return back()->with('success', 'Pregunta añadida.');
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question): RedirectResponse
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string'],
            'question_code' => ['nullable', 'string', 'max:30'],
            'order'         => ['required', 'integer', 'min:0'],
            'is_required'   => ['boolean'],
        ]);

        $question->update($validated);

        return back()->with('success', 'Pregunta actualizada.');
    }

    public function destroyQuestion(AssessmentQuestion $question): RedirectResponse
    {
        $assessmentId = $question->assessment_id;
        $question->delete();

        return redirect()
            ->route('admin.assessments.show', $assessmentId)
            ->with('success', 'Pregunta eliminada.');
    }

    // ----------------------------------------------------------------
    // Options
    // ----------------------------------------------------------------

    public function storeOption(Request $request, AssessmentQuestion $question): RedirectResponse
    {
        $validated = $request->validate([
            'option_text' => ['required', 'string', 'max:255'],
            'score_value' => ['required', 'integer'],
        ]);

        $order = $question->options()->max('order') + 1;
        $question->options()->create(array_merge($validated, ['order' => $order]));

        return back()->with('success', 'Opción añadida.');
    }

    public function destroyOption(AssessmentOption $option): RedirectResponse
    {
        $assessmentId = $option->question->assessment_id;
        $option->delete();

        return redirect()
            ->route('admin.assessments.show', $assessmentId)
            ->with('success', 'Opción eliminada.');
    }

    // ----------------------------------------------------------------
    // Rules
    // ----------------------------------------------------------------

    public function storeRule(Request $request, Assessment $assessment): RedirectResponse
    {
        $validated = $request->validate([
            'min_score'           => ['required', 'integer', 'min:0'],
            'max_score'           => ['required', 'integer', 'gte:min_score'],
            'severity_label'      => ['required', 'string', 'max:60'],
            'interpretation_text' => ['required', 'string'],
            'color_code'          => ['nullable', 'string', 'max:10', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $assessment->rules()->create($validated);

        return back()->with('success', 'Regla de interpretación añadida.');
    }

    public function destroyRule(AssessmentRule $rule): RedirectResponse
    {
        $assessmentId = $rule->assessment_id;
        $rule->delete();

        return redirect()
            ->route('admin.assessments.show', $assessmentId)
            ->with('success', 'Regla eliminada.');
    }
}
