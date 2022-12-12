//
//  QuestionsViewModel.swift
//  Voting
//
//  Created by Szabolcs Tóth on 16.10.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import Foundation

@MainActor
final class QuestionsViewModel: ObservableObject {
    
    enum State {
        case na
        case loading
        case succes(data: [Question])
        case failed(error: Error)
    }
    
    // Private properties
    private let service: QuestionService
    @Published private(set) var state: State = .na
    @Published var hasError: Bool = false
    
    init(service: QuestionService) {
        self.service = service
    }
    
    func getQuestions() async {
        self.hasError = false
        self.state = .loading
        
        do {
            let questions = try await service.fetchQuestions()
            self.state = .succes(data: questions)
        } catch {
            self.hasError = true
            self.state = .failed(error: error)
        }
    }
}
