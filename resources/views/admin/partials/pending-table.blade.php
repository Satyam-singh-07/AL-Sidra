 <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Applicant Details</th>
                                                <th>Category & Place</th>
                                                <th>Active Status</th>
                                                <th>KYC Documents</th>
                                                <th>Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($pendingMembers as $index => $member)
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
                                                            {{-- <small class="text-muted">Member ID: M{{ str_pad($member->id, 3, '0', STR_PAD_LEFT) }}</small> --}}
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
                                                        @if($member->memberProfile->place)
                                                            <i class="fas fa-map-marker-alt me-1"></i>
                                                            {{ $member->memberProfile->place->name }}
                                                        @else
                                                            -
                                                        @endif
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
                                                    <button
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewKycModal"
                                                        data-member-id="{{ $member->id }}"
                                                    >
                                                        <i class="fas fa-eye me-1"></i>View KYC
                                                    </button>
                                                </td>

                                                <td>
                                                    <div class="registration-date">
                                                        {{ optional($member->memberProfile->kyc)->submitted_at?->format('Y-m-d') }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ optional($member->memberProfile->kyc)->submitted_at?->diffForHumans() }}
                                                    </small>
                                                </td>

                                                <td class="action-buttons">
                                                    <button
                                                        class="btn btn-sm btn-success"
                                                        onclick="approveMember({{ $member->id }})"
                                                    >
                                                        <i class="fas fa-check me-1"></i>Approve
                                                    </button>

                                                    <button
                                                        class="btn btn-sm btn-danger"
                                                        onclick="rejectMember({{ $member->id }})"
                                                    >
                                                        <i class="fas fa-times me-1"></i>Reject
                                                    </button>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    No pending members found
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    @include('admin.partials.pagination', ['paginator' => $pendingMembers])
                                </div>
                            </div>