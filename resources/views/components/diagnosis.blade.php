<h2 class="text-2xl font-medium mb-4">Worklist</h2>

<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Date & Time</th>
                <th scope="col" class="px-6 py-3">Worklist Id</th>
                <th scope="col" class="px-6 py-3">Patient Name</th>
                <th scope="col" class="px-6 py-3">Referral</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($diagnosis as $diagnose)
                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                    <td class="px-6 py-4">{{ $diagnose->patient->created_at }}</td>
                    <td class="px-6 py-4">{{ $diagnose->id }}</td>
                    <td class="px-6 py-4">{{ $diagnose->patient->name }}</td>
                    <td class="px-6 py-4">{{ $diagnose->referral }}</td>
                    <td class="px-6 py-4">{{ $diagnose->status }}</td>
                    <td class="px-6 py-4">
                        <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Review</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
