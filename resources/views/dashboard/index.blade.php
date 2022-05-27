<!--suppress HtmlUnknownTag -->
<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>
    <div class="row">
        <div class="col">
            <div class="card dashboard-card border-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">{{ __('Total projects') }}</div>
                            <div class="h5 mb-0 fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/dashboard/stats') !!}" data-xhr-value="total-projects"><span class="placeholder col-4">&nbsp;</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card dashboard-card border-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">{{ __('Total splogs') }}</div>
                            <div class="h5 mb-0 fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/dashboard/stats') !!}" data-xhr-value="total-splogs"><span class="placeholder col-4">&nbsp;</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card dashboard-card border-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">{{ __('Total keywords') }}</div>
                            <div class="h5 mb-0 fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/dashboard/stats') !!}" data-xhr-value="total-keywords"><span class="placeholder col-4">&nbsp;</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card dashboard-card border-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">{{ __('Pending jobs') }}</div>
                            <div class="h5 mb-0 fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/dashboard/jobs') !!}" data-xhr-value><span class="placeholder col-4">&nbsp;</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col">
            <div class="card dashboard-card border-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">{{ __('Markov Cache Size') }}</div>
                            <div class="h5 mb-0 fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/dashboard/cache') !!}" data-xhr-value="markov_matrix"><span class="placeholder col-4">&nbsp;</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card dashboard-card border-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row g-0 align-items-center">
                        <div class="col me-2">
                            <div class="text-xs fw-bold text-primary text-uppercase mb-1">{{ __('Article Cache Size') }}</div>
                            <div class="h5 mb-0 fw-bold gray-dark placeholder-glow xhr-magic" data-xhr="{!! route('api/dashboard/cache') !!}" data-xhr-value="articles"><span class="placeholder col-4">&nbsp;</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
