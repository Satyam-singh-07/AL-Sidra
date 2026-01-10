<div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Applicant Details</th>
                                                <th>Category & Place</th>
                                                <th>Active Status</th>
                                                <th>Rejected By</th>
                                                <th>Rejected Date</th>
                                                <th>Reason</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @forelse ($rejectedMembers as $index => $member)
                                        <tr>
                                            <td class="text-muted">{{ $index + 1 }}</td>

                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar me-3">
                                                        {{ strtoupper(substr($member->name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="user-name">{{ $member->name }}</div>
                                                        <div class="phone-number">{{ $member->phone }}</div>
                                                        {{-- <small class="text-muted">
                                                            Member ID: M{{ str_pad($member->id, 3, '0', STR_PAD_LEFT) }}
                                                        </small> --}}
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <div class="mb-1">
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info">
                                                        {{ $member->memberProfile->category->name }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small">
                                                    {{ optional($member->memberProfile->place)->name ?? '-' }}
                                                </div>
                                            </td>

                                            <td>
                                                    <form
                                                        action="{{ route('members.toggle-status', $member->id) }}"
                                                        method="POST"
                                                        class="d-inline"
                                                    >
                                                        @csrf

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm {{ $member->status === 'active' ? 'btn-success' : 'btn-secondary' }}"
                                                        >
                                                            {{ ucfirst($member->status) }}
                                                        </button>
                                                    </form>
                                                </td>
                                                
                                            <td>
                                                <div class="small">Admin</div>
                                                <small class="text-muted">System</small>
                                            </td>

                                            <td>
                                                <div class="registration-date">
                                                    {{ $member->memberProfile->updated_at->format('Y-m-d') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ $member->memberProfile->updated_at->diffForHumans() }}
                                                </small>
                                            </td>

                                            <td>
                                                <small class="text-muted">
                                                    {{ $member->memberProfile->rejection_reason ?? '—' }}
                                                </small>
                                            </td>

                                            <td>
                                                <span class="status-badge status-rejected">Rejected</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No rejected members found
                                            </td>
                                        </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    @include('admin.partials.pagination', ['paginator' => $rejectedMembers])
                                </div>
                            </div>